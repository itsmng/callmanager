/**
 * CallManager - Prefill requester on ticket creation form
 *
 * Behavior:
 * - Runs on /front/ticket.form.php
 * - Requires query params: callmanager=1 & userid
 * - Sets hidden input or select box for requester
 * - No-ops safely if elements cannot be found.
 */
(function () {
  try {
    var path = (window.location.pathname || '');
    if (!/\/front\/ticket\.form\.php$/.test(path)) return;

    var params = new URLSearchParams(window.location.search);
    if (params.get('callmanager') !== '1') return;

    var userId = params.get('userid');
    if (!userId) return;

    // Avoid re-running
    if (window.__cmRequesterSet) return;
    window.__cmRequesterSet = true;

    function trigger(el, type) {
      if (!el) return;
      try {
        var evt = new Event(type, { bubbles: true, cancelable: true });
        el.dispatchEvent(evt);
      } catch (e) {
        // IE fallback
        var evt2 = document.createEvent('Event');
        evt2.initEvent(type, true, true);
        el.dispatchEvent(evt2);
      }
    }

    function setHiddenRequesterById(id) {
      // Common hidden input name/id candidates
      var candidates = [
        '#users_id_recipient',
        '#users_id_requester',
        'input[name="users_id_recipient"]',
        'input[name="users_id_requester"]',
        '#itil_users_id_recipient',
        'input[name="itil_users_id_recipient"]'
      ];
      for (var i = 0; i < candidates.length; i++) {
        var el = document.querySelector(candidates[i]);
        if (el) {
          el.value = String(id);
          trigger(el, 'input');
          trigger(el, 'change');
          return true;
        }
      }
      return false;
    }

    function setSelectRequesterById(id) {
      var selects = document.querySelectorAll('select');
      for (var i = 0; i < selects.length; i++) {
        var s = selects[i];
        // Heuristic: requester-related selects may have these in name/id/class
        var hint = (s.name || '') + ' ' + (s.id || '') + ' ' + (s.className || '');
        if (/requester|recipient|users_id/i.test(hint)) {
          var opt = s.querySelector('option[value="' + id + '"]');
          if (!opt) {
            opt = document.createElement('option');
            opt.value = String(id);
            opt.textContent = '#' + id;
            s.appendChild(opt);
          }
          s.value = String(id);
          trigger(s, 'change');
          trigger(s, 'input');

          // If Select2 is used, attempt Select2 event triggers
          if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
            try {
              jQuery(s).val(String(id)).trigger('change');
            } catch (e) {}
          }
          return true;
        }
      }
      return false;
    }

    function trySetRequester() {
      if (setHiddenRequesterById(userId)) return true;
      if (setSelectRequesterById(userId)) return true;
      return false;
    }

    if (!trySetRequester()) {
      // If not found, observe DOM changes to try again
      var observer = new MutationObserver(function () {
        if (trySetRequester()) {
          observer.disconnect();
        }
      });
      observer.observe(document.documentElement, { childList: true, subtree: true });

      // Safety timeout to stop observing after some time
      setTimeout(function () {
        if (observer) observer.disconnect();
      }, 8000);
    }
  } catch (e) {
    console.warn('CallManager inject-ticketcreate-prefill error:', e);
  }
})();
