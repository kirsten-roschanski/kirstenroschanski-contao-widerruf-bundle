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
              if (message) {
                message.textContent = block.getAttribute('data-error-missing-altcha') || 'Bitte Anti-Spam-Prüfung abschließen.';
              }

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
            return response.json();
          })
          .then(function (json) {
            if (!json.success) {
              throw new Error(json.message || block.getAttribute('data-error-submit') || 'Widerruf konnte nicht übermittelt werden.');
            }

            if (message) {
              message.textContent = block.getAttribute('data-success-message') || block.getAttribute('data-success-fallback') || 'Widerruf erfolgreich übermittelt.';
            }

            form.reset();
          })
          .catch(function (error) {
            if (error && error.message === '__ALTCHA_MISSING__') {
              return;
            }

            if (message) {
              message.textContent = error.message;
            }
          })
          .finally(function () {
            isSubmitting = false;
          });
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindWiderruf();
  });
})();
