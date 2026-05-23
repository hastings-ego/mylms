const App = {
    state: {
        admin: null,
        data: { projects: [], users: [], metrics: {} },
        activeTab: 'overview'
    },

    elements: {
        appRoot: null,
        modalRoot: null
    },

    init() {
        this.elements.appRoot = document.getElementById('app');
        this.elements.modalRoot = document.getElementById('modal-root');
        
        // Expose to window for inline HTML onclick handlers
        window.App = this;

        this.loadDashboardData();
    },

    render() {
        if (!this.state.admin) {
            this.elements.appRoot.innerHTML = Views.renderLogin();
            this.setupLoginHandler();
        } else {
            this.elements.appRoot.innerHTML = Views.renderLayout(this.state);
            this.renderTabContent();
        }
    },

    renderTabContent() {
        const container = document.getElementById('tab-content');
        if (!container) return;

        if (this.state.activeTab === 'overview') {
            container.innerHTML = Views.renderOverview(this.state);
        } else if (this.state.activeTab === 'projects') {
            container.innerHTML = Views.renderProjects(this.state);
        } else if (this.state.activeTab === 'clients') {
            container.innerHTML = Views.renderClients(this.state);
        }
    },

    setTab(tab) {
        this.state.activeTab = tab;
        this.render(); // Re-renderlayout for active classes
    },

    setupLoginHandler() {
        const form = document.getElementById('login-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errBox = document.getElementById('login-error');
            const btn = form.querySelector('button[type="submit"]');
            errBox.classList.add('hidden');
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            const val = await ApiService.login(email, password);

            if (val.success && val.user.role === 'admin') {
                this.state.admin = val.user;
                await this.loadDashboardData();
            } else {
                errBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (val.message || 'Invalid credentials or access denied.');
                errBox.classList.remove('hidden');
            }
            btn.innerHTML = originalText;
        });
    },

    async loadDashboardData() {
        const res = await ApiService.getDashboard();
        if (res.success) {
            this.state.admin = true; // Mark as logged in implicitly
            this.state.data = { 
                projects: res.projects || [], 
                users: res.users || [], 
                metrics: res.metrics || {} 
            };
            this.render();
        } else {
            this.state.admin = null;
            this.render();
        }
    },

    async doLogout() {
        await ApiService.logout();
        this.state.admin = null;
        this.state.activeTab = 'overview';
        this.render();
    },

    openModal(id) {
        const p = this.state.data.projects.find(x => x.id === id);
        if (!p) return;
        this.elements.modalRoot.innerHTML = Views.renderModal(p);
    },

    closeModal() {
        this.elements.modalRoot.innerHTML = '';
    },

    async saveProject(id) {
        const payload = {
            project_id: id,
            status: document.getElementById('mod-status').value,
            admin_notes: document.getElementById('mod-notes').value,
            published_url: document.getElementById('mod-pub').value,
            app_url: document.getElementById('mod-app').value
        };

        const res = await ApiService.updateProject(payload);
        if (res.success) {
            this.closeModal();
            this.loadDashboardData(); 
        } else {
            alert('Error updating project: ' + res.message);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());
