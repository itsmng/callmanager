/**
 * CallManager - Inject "Create ticket for this user" button on ticket list
 *
 * Behavior:
 * - Runs on /front/ticket.php
 * - Requires query params: callmanager=1 & userid
 * - Injects a button above the ticket list
 * - On click: navigates to /front/ticket.form.php?callmanager=1&userid=...
 */
(function () {
  try {
    var path = (window.location.pathname || '');
    if (!/\/front\/ticket\.php$/.test(path)) return;

    var params = new URLSearchParams(window.location.search);
    if (params.get('callmanager') !== '1') return;

    var userId = params.get('userid');
    if (!userId) return;

    // Test available containers
    function findToolbarContainer() {
      // These are tested in order
      var selectors = [
        '.page-title .controls',
        '.page-title',
        '.header .controls',
        '.header',
        '.actions',
        '.search_page',
        '.buttonsBar',
        '#page .controls',
        '#page'
      ];
      for (var i = 0; i < selectors.length; i++) {
        var el = document.querySelector(selectors[i]);
        if (el) return el;
      }
      // If none found, place above the results table if exists
      var table = document.querySelector('table');
      if (table && table.parentNode) return table.parentNode;
      return document.body;
    }

    var container = findToolbarContainer();
    if (!container) return;

    // Prevent duplicate insertion
    if (document.getElementById('cm-create-ticket-for-user')) return;

    let btn;
    let cont;
    if (document.querySelector('.btn')) {
      btn = document.createElement('button');
      btn.id = 'cm-create-ticket-for-user';
      btn.type = 'button';
      btn.className = 'btn btn-primary d-block mx-auto mb-2';
      btn.title = __('Open ticket creation form', 'callmanager');
      btn.textContent = __('Create ticket for this user', 'callmanager');
      cont = btn;
    } else {
      cont = document.createElement('div');
      cont.className = 'center';
      cont.style.marginBottom = '10px';
      btn = document.createElement('button');
      btn.id = 'cm-create-ticket-for-user';
      btn.type = 'button';
      btn.className = 'vsubmit';
      btn.title = __('Open ticket creation form', 'callmanager');
      btn.style.fontSize = '1.5em'; // Make the button text larger
      btn.style.padding = '10px 20px'; // Add padding to make the button larger
      btn.innerHTML = `<i class="fas fa-plus" style="margin-right: 10px;"></i>${__('Create ticket for this user', 'callmanager')}`;
      cont.appendChild(btn);
    }

    btn.addEventListener('click', function () {
      // Build redirect to plugin endpoint that will impersonate and then send us to the standard form
      const url = `/plugins/callmanager/front/impersonate_and_redirect.php?caller_users_id=${encodeURIComponent(userId)}`;

      window.location.href = url;
    });

    container.insertBefore(cont, container.firstChild);
  } catch (e) {
    console.warn('CallManager inject-ticketlist-action error:', e);
  }
})();
