const { h, render } = preact;
const { useState, useEffect } = preactHooks;
const html = htm.bind(h);

const SearchForm = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const rioParam = urlParams.get('rio');
    if (rioParam) {
      handleSearch(rioParam);
    }
  }, []);

  const handleSearch = async (rio) => {
    if (!rio.trim()) {
      setError(__('RIO number is required', 'callmanager'));
      return;
    }

    setLoading(true);
    setError('');

    try {
      const currentPath = window.location.pathname;
      const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));

      const apiUrl = `${baseUrl}/plugins/callmanager/api.php/users/${rio}`;
      const response = await fetch(apiUrl, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'same-origin'
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();

      if (result.users && result.users.length > 0) {
        const normalized = result.users.map(user => ({
          id: user.id,
          phone: user.phone || '',
          lastname: user.lastname || '',
          firstname: user.firstname || '',
          rio: user.rio || rio,
          email: user.email || '',
          entity: user.entity || '',
          location: user.location || ''
        }));

        setUsers(normalized);
      } else {
        setUsers([]);
        setError(__('No user found for this RIO', 'callmanager'));
      }
    } catch (err) {
      setError(__('Connection error: ', 'callmanager') + err.message);
      console.error('Search error:', err);
    } finally {
      setLoading(false);
    }
  };

  const viewUserTickets = (userId, rio) => {
    const currentPath = window.location.pathname;
    const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));

    const baseQuery = `contains[0]=${userId}&criteria[0][field]=4&criteria[0][searchtype]=equals&criteria[0][value]=${userId}&itemtype=Ticket&start=0`;
    const ctx = `callmanager=1&userid=${encodeURIComponent(userId)}${rio ? `&rio=${encodeURIComponent(rio)}` : ''}`;
    const ticketUrl = `${baseUrl}/front/ticket.php?${baseQuery}&${ctx}`;

    window.location.href = ticketUrl;
  };

  const viewUserProfile = (userId) => {
    const currentPath = window.location.pathname;
    const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
    
    const userUrl = `${baseUrl}/front/user.form.php?id=${encodeURIComponent(userId)}`;
    window.open(userUrl, '_blank');
  };

  return html`
      <div class="callmanager-container">
        <div class="callmanager-header">
          <h1>${__('Call Manager - User Results', 'callmanager')}</h1>
        </div>

        ${error && html`<div class="alert alert-danger">${error}</div>`}

        ${users.length > 0 && html`
          <div class="search-results cm-card">
            <div class="cm-card-header">
              <h2>${users.length} ${__('user(s) found', 'callmanager')}</h2>
            </div>
            <div class="cm-card-body">
              <div class="table-responsive">
                <table class="tab_cadrehov table table-striped callmanager-results-table">
                  <thead>
                    <tr class="tab_bg_2">
                      <th>${__('Last name', 'callmanager')}</th>
                      <th>${__('First name', 'callmanager')}</th>
                      <th>${__('Email', 'callmanager')}</th>
                      <th>${__('Entity', 'callmanager')}</th>
                      <th>${__('Location', 'callmanager')}</th>
                      <th>${__('Actions', 'callmanager')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${users.map(user => html`
                      <tr key=${user.id}>
                        <td>${user.lastname || __('N/A', 'callmanager')}</td>
                        <td>${user.firstname || __('N/A', 'callmanager')}</td>
                        <td>${user.email || __('N/A', 'callmanager')}</td>
                        <td>${user.entity || __('N/A', 'callmanager')}</td>
                        <td>${user.location || __('N/A', 'callmanager')}</td>
                        <td style="white-space:nowrap;">
                          <button
                            class="btn btn-secondary btn-sm"
                            onClick=${() => viewUserTickets(user.id, user.rio)}
                            title=${__("View this user's tickets", 'callmanager')}
                            style="margin-right:6px;"
                          >
                            ${__('View tickets', 'callmanager')}
                          </button>
                          <button
                            class="btn btn-primary btn-sm"
                            onClick=${() => {
                              const currentPath = window.location.pathname;
                              const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
                              const url = `${baseUrl}/plugins/callmanager/front/impersonate_and_redirect.php?caller_users_id=${encodeURIComponent(user.id)}`;
                              window.location.href = url;
                            }}
                            title=${__("Open ticket creation form", 'callmanager')}
                            style="margin-right:6px;"
                          >
                            ${__("Create ticket for this user", 'callmanager')}
                          </button>
                          <button
                            class="btn btn-info btn-sm"
                            onClick=${() => viewUserProfile(user.id)}
                            title=${__("View user profile", 'callmanager')}
                          >
                            ${__('View profile', 'callmanager')}
                          </button>
                        </td>
                      </tr>
                    `)}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `}
        
        ${!loading && users.length === 0 && !error && html`
          <div class="cm-card">
            <div class="cm-card-body">
              <p>${__('No search results. Please provide a RIO parameter in the URL.', 'callmanager')}</p>
            </div>
          </div>
        `}

        ${!loading && users.length === 0 && error && error.includes(__('No user found for this RIO', 'callmanager')) && html`
          <div class="cm-card">
            <div class="cm-card-header">
              <h3>${__('User not found', 'callmanager')}</h3>
            </div>
            <div class="cm-card-body">
              <p>${__('No user found for this RIO number. Would you like to create a new user?', 'callmanager')}</p>
              <div class="text-center" style="margin-top: 20px;">
                <button
                  class="btn btn-success btn-lg"
                  onClick=${() => {
                    const urlParams = new URLSearchParams(window.location.search);
                    const rio = urlParams.get('rio');
                    if (rio) {
                      const currentPath = window.location.pathname;
                      const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
                      const createUserUrl = `${baseUrl}/front/user.form.php?rio=${encodeURIComponent(rio)}`;
                      window.location.href = createUserUrl;
                    }
                  }}
                  title=${__("Create a new user with this RIO number", 'callmanager')}
                >
                  <i class="fas fa-user-plus" style="margin-right: 8px;"></i>
                  ${__('Create new user with this RIO', 'callmanager')}
                </button>
              </div>
            </div>
          </div>
        `}
      </div>
    `;
};

const CallManagerConfig = {
  component: SearchForm,

  init: () => {
    const container = document.getElementById('plugin_callmanager_ui');
    if (container) {
      render(html`<${SearchForm} />`, container);
    }
  },

  api: {
    getUserByRio: (rio) => {
      const currentPath = window.location.pathname;
      const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
      return `${baseUrl}/plugins/callmanager/api.php/users/${rio}`;
    },
    getUserTickets: () => {
      const currentPath = window.location.pathname;
      const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
      return `${baseUrl}/plugins/callmanager/ajax/get_user_tickets.php`;
    },
    createTicket: () => {
      const currentPath = window.location.pathname;
      const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
      return `${baseUrl}/plugins/callmanager/ajax/create_ticket.php`;
    }
  },
};

if (typeof module !== 'undefined' && module.exports) {
  module.exports = CallManagerConfig;
} else {
  window.CallManagerConfig = CallManagerConfig;
}

document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    CallManagerConfig.init();
  }, 100); // Delay to ensure locales are loaded by base.js
});

const style = document.createElement('style');
style.textContent = CallManagerConfig.styles;
document.head.appendChild(style);