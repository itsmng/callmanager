/**
 * CallManager - Helper script for user creation with RIO pre-fill
 * 
 * This script runs on user.form.php to pre-fill RIO number and add hidden field
 */
(function() {
    if (!window.location.pathname.includes('/front/user.form.php')) {
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const rio = urlParams.get('rio');
    
    if (!rio) {
        return;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Pre-fill the login/name field with RIO number if storage method is 'name'
        fetch('/plugins/callmanager/api.php/config/rio_storage_method')
            .then(response => response.json())
            .then(data => {
                if (data.value === 'name') {
                    prefillUserLoginField(rio);
                }
            })
            .catch(error => {
                console.warn('CallManager: Could not fetch config, skipping name pre-fill', error);
            });

        // Handle form submission to add RIO number and set redirect flag
        setTimeout(function() {
            // Look for forms that are likely the user creation form
            const forms = document.querySelectorAll('form');
            let userForm = null;
            
            for (const form of forms) {
                // Skip search forms and other non-user forms
                if (form.action && (form.action.includes('/front/search.php') || 
                                   form.action.includes('/front/helpdesk'))) {
                    continue;
                }
                
                // Look for forms that contain user-related inputs
                const hasNameInput = form.querySelector('input[name="name"]');
                const hasUserInputs = form.querySelector('input[name*="user"]') || 
                                     form.querySelector('input[name*="login"]') ||
                                     form.querySelector('input[name*="realname"]');
                
                if (hasNameInput || hasUserInputs) {
                    userForm = form;
                    break;
                }
            }
            
            if (!userForm) {
                userForm = document.querySelector('form[action*="user.form.php"]') ||
                          document.querySelector('form[action*="/front/user"]');
            }
            
            if (userForm && rio) {
                // Add hidden input field to pass RIO number in POST data
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'callmanager_rio';
                hiddenInput.value = rio;
                userForm.appendChild(hiddenInput);
            } else {
                console.warn('CallManager: Could not set up form handler. User Form:', userForm, 'RIO:', rio);
            }
        }, 500);
    });

    function prefillUserLoginField(rio) {
        const loginField = document.querySelector('input[name="name"]');

        if (!loginField) {
            const allInputs = document.querySelectorAll('input[type="text"]');
            for (const input of allInputs) {
                const row = input.closest('tr') || input.closest('.form-group') || input.closest('div');
                if (row) {
                    const rowText = row.textContent.toLowerCase();
                    if (rowText.includes('identifiant') || 
                        rowText.includes('login') || 
                        (rowText.includes('nom') && rowText.includes('utilisateur'))) {
                        loginField = input;
                        break;
                    }
                }
            }
        }

        if (loginField) {
            loginField.value = rio;
            
            loginField.dispatchEvent(new Event('input', { bubbles: true }));
            loginField.dispatchEvent(new Event('change', { bubbles: true }));
            
            loginField.style.backgroundColor = '#e8f5e8';
            loginField.style.border = '2px solid #28a745';
        } else {
            console.warn('CallManager: Could not find login/name field to pre-fill');
        }
    }
})();;  