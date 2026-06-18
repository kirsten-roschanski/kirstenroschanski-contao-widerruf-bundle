(function () {
  if (window.__widerrufLoaded) {
    return;
  }

  window.__widerrufLoaded = true;

  function bindWiderruf() {
    document.querySelectorAll('[data-widerruf]').forEach(function (block) {
      var form = block.querySelector('[data-widerruf-form]');
      var message = block.querySelector('[data-widerruf-message]');

      if (!form) {
        return;
      }

      form.addEventListener('submit', function (event) {
        event.preventDefault();

        var payload = {
          order_uuid: (form.querySelector('[name="order_uuid"]') || {}).value || '',
          consumer_name: (form.querySelector('[name="consumer_name"]') || {}).value || '',
          contract_reference: (form.querySelector('[name="contract_reference"]') || {}).value || '',
          confirmation_email: (form.querySelector('[name="confirmation_email"]') || {}).value || '',
          altcha: (form.querySelector('[name="altcha"]') || {}).value || '',
        };

        if (!payload.altcha) {
          if (message) {
            message.textContent = block.getAttribute('data-error-missing-altcha') || 'Bitte Anti-Spam-Prüfung abschließen.';
          }

          return;
        }

        fetch(block.getAttribute('data-widerruf-url'), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(payload),
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
            if (message) {
              message.textContent = error.message;
            }
          });
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindWiderruf();
  });
})();
