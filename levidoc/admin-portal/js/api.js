const API_URL = 'http://localhost:7600/api.php';

const ApiService = {
    async request(action, method = 'GET', data = null) {
        const opts = { 
            method, 
            headers: { 'Content-Type': 'application/json' } 
        };
        if (data) opts.body = JSON.stringify(data);
        
        try {
            const res = await fetch(`${API_URL}?action=${action}`, opts);
            if (!res.ok) throw new Error('Network response was not ok');
            return await res.json();
        } catch (err) {
            console.error('API Error:', err);
            return { success: false, message: err.message };
        }
    },

    login(email, password) {
        return this.request('login', 'POST', { email, password });
    },

    logout() {
        return this.request('logout');
    },

    getDashboard() {
        return this.request('admin_dashboard');
    },

    updateProject(data) {
        return this.request('update_project_status', 'POST', data);
    }
};

window.ApiService = ApiService;
