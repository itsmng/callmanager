const { h, render } = preact;
const { useState, useEffect } = preactHooks;

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
            setError('Le numéro RIO est obligatoire');
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
                setError('Aucun utilisateur trouvé avec ce RIO');
            }
        } catch (err) {
            setError('Erreur de connexion: ' + err.message);
            console.error('Search error:', err);
        } finally {
            setLoading(false);
        }
    };

    const viewUserTickets = (userId) => {
        const currentPath = window.location.pathname;
        const baseUrl = currentPath.substring(0, currentPath.indexOf('/plugins/'));
        
        const ticketUrl = `${baseUrl}/front/ticket.php?contains%5B0%5D=${userId}&criteria%5B0%5D%5Bfield%5D=4&criteria%5B0%5D%5Bsearchtype%5D=equals&criteria%5B0%5D%5Bvalue%5D=${userId}&itemtype=Ticket&start=0`;
        
        window.location.href = ticketUrl;
    };

    return h('div', { className: 'callmanager-container' },

        h('div', { className: 'callmanager-header' },
            h('h1', null, 'Call Manager - Recherche utilisateur'),
            h('p', null, 'Recherchez un utilisateur à partir de son numéro RIO')
        ),

        h('div', { className: 'search-form card' },
            h('div', { className: 'card-header' },
                h('h2', null, 'Critères de recherche')
            ),
            h('div', { className: 'card-body' },
                h('form', { 
                    onSubmit: (e) => { e.preventDefault(); handleSearch(); }
                },
                    h('div', { className: 'form-group' },
                        h('label', { htmlFor: 'rio' }, 'N° RIO *'),
                        h('input', {
                            type: 'text',
                            id: 'rio',
                            className: 'form-control',
                            placeholder: 'Numéro RIO de l\'appelant',
                            value: formData.rio,
                            onInput: (e) => handleInputChange('rio', e.target.value),
                            required: true,
                            maxLength: 20
                        })
                    ),

                    h('div', { className: 'form-actions' },
                        h('button', {
                            type: 'submit',
                            className: 'btn btn-primary',
                            disabled: loading
                        }, loading ? 'Recherche...' : 'Rechercher'),
                        
                        h('button', {
                            type: 'button',
                            className: 'btn btn-secondary ml-2',
                            onClick: () => {
                                setFormData({ rio: '', firstname: '', lastname: '', phone: '' });
                                setUsers([]);
                                setError('');
                            }
                        }, 'Effacer')
                    )
                )
            )
        ),

        error && h('div', { className: 'alert alert-danger' }, error),

        users.length > 0 && h('div', { className: 'search-results card' },
            h('div', { className: 'card-header' },
                h('h2', null, `${users.length} utilisateur(s) trouvé(s)`)
            ),
            h('div', { className: 'card-body' },
                h('div', { className: 'table-responsive' },
                    h('table', { className: 'table table-striped' },
                        h('thead', null,
                            h('tr', null,
                                h('th', null, 'ID'),
                                h('th', null, 'Nom'),
                                h('th', null, 'RIO'),
                                h('th', null, 'Action')
                            )
                        ),
                        h('tbody', null,
                            users.map(user => 
                                h('tr', { key: user.id },
                                    h('td', null, user.id),
                                    h('td', null, user.lastname || user.name || 'N/A'),
                                    h('td', null, user.rio),
                                    h('td', null,
                                        h('button', {
                                            className: 'btn btn-primary btn-sm',
                                            onClick: () => viewUserTickets(user.id),
                                            title: 'Voir les tickets de cet utilisateur'
                                        }, 'Voir les tickets')
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
    );
};

const CallManagerConfig = {
    component: SearchForm,

    init: () => {
        const container = document.getElementById('plugin_callmanager_config_form');
        if (container) {
            render(h(SearchForm), container);
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