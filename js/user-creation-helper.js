/**
 * CallManager - Helper script for user creation with RIO pre-fill
 * 
 * This script runs on user.form.php to pre-fill RIO number if provided in URL
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
        // Add a notice about the RIO number
        addRioNotice(rio);
        
        // Pre-fill the login/name field with RIO number
        prefillUserLoginField(rio);
        
        localStorage.setItem('callmanager_pending_rio', rio);
    });

    function addRioNotice(rio) {
        const formContainer = document.querySelector('form[name="asset_form"]') || 
                             document.querySelector('form') || 
                             document.querySelector('.tab_cadre_fixe');
        
        if (!formContainer) {
            return;
        }
    }

    function prefillUserLoginField(rio) {
        const possibleSelectors = [
            'input[name="name"]',
            'input[name="login"]',
            '#textfield_name',
            'input[id*="name"]',
            'input[placeholder*="login"]',
            'input[placeholder*="identifiant"]'
        ];

        let loginField = null;
        
        for (const selector of possibleSelectors) {
            loginField = document.querySelector(selector);
            if (loginField) {
                break;
            }
        }

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
            
            console.log('CallManager: Login field pre-filled with RIO:', rio);
        } else {
            console.warn('CallManager: Could not find login/name field to pre-fill');
        }
    }

    // Handle form submission to add RIO number
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[name="asset_form"]') || document.querySelector('form');
        
        if (form && rio) {
            form.addEventListener('submit', function(e) {
                // Add hidden field with RIO number
                const rioInput = document.createElement('input');
                rioInput.type = 'hidden';
                rioInput.name = 'callmanager_rio';
                rioInput.value = rio;
                form.appendChild(rioInput);
            });
        }
    });
})();