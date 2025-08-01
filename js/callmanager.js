const { h, render } = preact;
const { useState, useEffect } = preactHooks;
const html = htm.bind(h);

const SearchForm = () => {
    const [formData, setFormData] = useState({
        rio: '',
        firstname: '',
        lastname: '',
        phone: ''
    });
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const rioParam = urlParams.get('rio');
        if (rioParam) {
            setFormData(prev => ({ ...prev, rio: rioParam }));
            handleSearch({ ...formData, rio: rioParam });
        }
    }, []);

    const handleInputChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    const handleSearch = async (searchData = formData) => {
        if (!searchData.rio.trim()) {
            setError(__('RIO number is required', 'callmanager'));
            return;
        }

        setLoading(true);
        setError('');

        try {
            const currentPath = window.location.pathname;
            const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
            
            const apiUrl = `${baseUrl}/plugins/callmanager/api.php/users/${searchData.rio}`;
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
                const enrichedUsers = result.users.map(user => ({
                    id: user.id,
                    firstname: user.firstname || '',
                    lastname: user.lastname || user.name || '',
                    email: user.email || '',
                    phone: user.phone || '',
                    entity: user.entity || '',
                    rio: searchData.rio
                }));
                
                setUsers(enrichedUsers);
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

    const viewUserTickets = (userId) => {
        const currentPath = window.location.pathname;
        const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));

        const ticketUrl = `${baseUrl}/front/ticket.php?contains[0]=${userId}&criteria[0][field]=4&criteria[0][searchtype]=equals&criteria[0][value]=${userId}&itemtype=Ticket&start=0`;

        window.location.href = ticketUrl;
    };

    return html`
      <div class="callmanager-container">
        <div class="callmanager-header">
          <h1>${__('Call Manager - User search', 'callmanager')}</h1>
          <p>${__('Search a user by RIO number', 'callmanager')}</p>
        </div>

        <div class="search-form card">
          <div class="card-header">
            <h2>${__('Search criteria', 'callmanager')}</h2>
          </div>
          <div class="card-body">
            <form onSubmit=${(e) => { e.preventDefault(); handleSearch(); }}>
              <div class="form-group">
                <label for="rio">${__('RIO number *', 'callmanager')}</label>
                <input
                  type="text"
                  id="rio"
                  class="form-control"
                  placeholder=${__('Caller RIO number', 'callmanager')}
                  value=${formData.rio}
                  onInput=${(e) => handleInputChange('rio', e.target.value)}
                  required
                  maxLength=${20}
                />
              </div>

              <div class="form-actions">
                <button
                  type="submit"
                  class="btn btn-primary"
                  disabled=${loading}
                >
                  ${loading ? __('Searching…', 'callmanager') : __('Search', 'callmanager')}
                </button>

                <button
                  type="button"
                  class="btn btn-secondary ml-2"
                  onClick=${() => {
                    setFormData({ rio: '', firstname: '', lastname: '', phone: '' });
                    setUsers([]);
                    setError('');
                  }}
                >
                  ${__('Clear', 'callmanager')}
                </button>
              </div>
            </form>
          </div>
        </div>

        ${error && html`<div class="alert alert-danger">${error}</div>`}

        ${users.length > 0 && html`
          <div class="search-results card">
            <div class="card-header">
              <h2>${users.length} ${__('user(s) found', 'callmanager')}</h2>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>${__('Name', 'callmanager')}</th>
                      <th>RIO</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${users.map(user => html`
                      <tr key=${user.id}>
                        <td>${user.id}</td>
                        <td>${user.lastname || user.name || __('N/A', 'callmanager')}</td>
                        <td>${user.rio}</td>
                        <td>
                          <button
                            class="btn btn-primary btn-sm"
                            onClick=${() => viewUserTickets(user.id)}
                            title=${__('View this user\'s tickets', 'callmanager')}
                          >
                            ${__('View tickets', 'callmanager')}
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
      </div>
    `;
};

const CallManagerConfig = {
    component: SearchForm,

    init: () => {
        const container = document.getElementById('plugin_callmanager_ui');
        if (container) {
            // Use htm template to render component
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
    CallManagerConfig.init();
});

const style = document.createElement('style');
style.textContent = CallManagerConfig.styles;
document.head.appendChild(style);
