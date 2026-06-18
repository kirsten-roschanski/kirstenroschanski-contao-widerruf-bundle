(function () {
  if (window.__widerrufLoaded) {
    return;
  }

  window.__widerrufLoaded = true;

  function bindWiderruf() {
    document.querySelectorAll('[data-widerruf]').forEach(function (block) {
      var form = block.querySelector('[data-widerruf-form]');
      var message = block.querySelector('[data-widerruf-message]');
      var isSubmitting = false;

      if (!form) {
        return;
      }

      var altchaEnabled = block.getAttribute('data-altcha-enabled') !== '0';

      function announceMessage(text, isError) {
        if (!message) {
          return;
        }

        message.hidden = false;
        message.setAttribute('role', isError ? 'alert' : 'status');
        message.setAttribute('aria-live', isError ? 'assertive' : 'polite');
        message.textContent = text;

        if (typeof message.focus === 'function') {
          message.focus({ preventScroll: false });
        }
      }

      function getAltchaPayload() {
        var altchaField = form.querySelector('[name="altcha"]');

        if (!altchaField || typeof altchaField.value !== 'string') {
          return '';
        }

        return altchaField.value.trim();
      }

      function verifyAltchaIfPossible() {
        var altchaField = form.querySelector('[name="altcha"]');

        if (!altchaField || typeof altchaField.verify !== 'function') {
          return Promise.resolve();
        }

        return Promise.resolve(altchaField.verify()).catch(function () {
          return null;
        });
      }

      form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (isSubmitting) {
          return;
        }

        isSubmitting = true;
        form.setAttribute('aria-busy', 'true');

        if (message) {
          message.textContent = '';
          message.hidden = true;
          message.setAttribute('role', 'status');
          message.setAttribute('aria-live', 'polite');
        }

        Promise.resolve()
          .then(function () {
            if (!altchaEnabled) {
              return null;
            }

            return verifyAltchaIfPossible();
          })
          .then(function () {
            var altchaPayload = altchaEnabled ? getAltchaPayload() : '';

            if (altchaEnabled && !altchaPayload) {
              announceMessage(block.getAttribute('data-error-missing-altcha') || 'Bitte Anti-Spam-Prüfung abschließen.', true);

              throw new Error('__ALTCHA_MISSING__');
            }

            var payload = {
              cte_id: (form.querySelector('[name="cte_id"]') || {}).value || '0',
              order_uuid: (form.querySelector('[name="order_uuid"]') || {}).value || '',
              consumer_name: (form.querySelector('[name="consumer_name"]') || {}).value || '',
              contract_reference: (form.querySelector('[name="contract_reference"]') || {}).value || '',
              confirmation_email: (form.querySelector('[name="confirmation_email"]') || {}).value || '',
              altcha: altchaPayload,
            };

            var body = new URLSearchParams();

            Object.keys(payload).forEach(function (key) {
              body.append(key, String(payload[key] || ''));
            });

            return fetch(block.getAttribute('data-widerruf-url'), {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
              },
              body: body.toString(),
            });
          })
          .then(function (response) {
            return response.text().then(function (text) {
              var json = null;

              if ('' !== text.trim()) {
                try {
                  json = JSON.parse(text);
                } catch (parseError) {
                  if (!response.ok) {
                    throw new Error((block.getAttribute('data-error-submit') || 'Widerruf konnte nicht übermittelt werden.') + ' (HTTP ' + response.status + ')');
                  }

                  throw new Error('Unerwartete Serverantwort. Bitte Seite neu laden und erneut versuchen.');
                }
              }

              if (!response.ok) {
                throw new Error((json && json.message) || (block.getAttribute('data-error-submit') || 'Widerruf konnte nicht übermittelt werden.') + ' (HTTP ' + response.status + ')');
              }

              if (!json || 'object' !== typeof json) {
                throw new Error('Unerwartete Serverantwort. Bitte Seite neu laden und erneut versuchen.');
              }

              return json;
            });
          })
          .then(function (json) {
            if (!json.success) {
              throw new Error(json.message || block.getAttribute('data-error-submit') || 'Widerruf konnte nicht übermittelt werden.');
            }

            announceMessage(block.getAttribute('data-success-message') || block.getAttribute('data-success-fallback') || 'Widerruf erfolgreich übermittelt.', false);

            form.reset();
          })
          .catch(function (error) {
            if (error && error.message === '__ALTCHA_MISSING__') {
              return;
            }

            announceMessage(error.message, true);
          })
          .finally(function () {
            isSubmitting = false;
            form.setAttribute('aria-busy', 'false');
          });
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindWiderruf();
  });
})();
