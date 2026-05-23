// Utility for escaping HTML
function esc(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/[&<>"']/g, m => {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
}

function getStatusBadge(status) {
    if (status === 'pending') return '<span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide rounded-full bg-amber-100 text-amber-700 shadow-sm border border-amber-200"><i class="fas fa-hourglass-half mr-1"></i>Pending</span>';
    if (status === 'approved') return '<span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide rounded-full bg-emerald-100 text-emerald-700 shadow-sm border border-emerald-200"><i class="fas fa-check mr-1"></i>Approved</span>';
    if (status === 'construction') return '<span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide rounded-full bg-indigo-100 text-indigo-700 shadow-sm border border-indigo-200"><i class="fas fa-hammer mr-1"></i>Building</span>';
    return `<span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide rounded-full bg-slate-100 text-slate-700 border border-slate-200">${esc(status)}</span>`;
}

// ==== HTML View Templates ====
const Views = {
    renderLogin: () => `
        <div class="flex items-center justify-center min-h-screen relative overflow-hidden" style="background: radial-gradient(circle at 100% 0%, #1e1b4b 0%, #0f172a 100%);">
            <!-- decorative bg -->
            <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
            
            <div class="max-w-md w-full relative z-10 px-4">
                <div class="bg-white/95 backdrop-blur-xl rounded-[2rem] shadow-2xl p-8 sm:p-10 border border-white/20 fade-in">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-indigo-500/30 mb-4 transform -rotate-6">
                            <i class="fas fa-shield-alt text-2xl text-white transform rotate-6"></i>
                        </div>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Levidoc Admin</h2>
                        <p class="text-slate-500 mt-2 font-medium">Control panel authentication</p>
                    </div>
                    <form id="login-form" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5 ml-1">Email address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-4 top-3.5 text-slate-400"></i>
                                <input id="email" type="email" class="w-full border-2 border-slate-200 rounded-xl pl-11 pr-4 py-3 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all font-medium" required value="admin@levidoc.com">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5 ml-1">Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                                <input id="password" type="password" class="w-full border-2 border-slate-200 rounded-xl pl-11 pr-4 py-3 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all font-medium" required value="admin123">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-slate-800 transition-all transform hover:-translate-y-0.5 shadow-xl shadow-slate-900/20 active:translate-y-0">
                            Secure Login <i class="fas fa-arrow-right ml-2 text-sm opacity-80"></i>
                        </button>
                        <div id="login-error" class="bg-rose-50 text-rose-600 text-sm font-semibold rounded-lg p-3 hidden text-center border border-rose-100 flex items-center justify-center gap-2"></div>
                    </form>
                </div>
                <div class="text-center mt-6 text-slate-400/80 text-xs font-medium">
                    &copy; 2026 Levidoc Agency. Secure SSL Connection.
                </div>
            </div>
        </div>
    `,

    renderLayout: (state) => `
        <div class="flex flex-col md:flex-row min-h-screen bg-slate-50">
            <!-- Sidebar Desktop -->
            <aside class="w-full md:w-[260px] bg-slate-900 text-slate-300 flex-col hidden md:flex fixed h-full z-20 overflow-y-auto border-r border-slate-800">
                <div class="p-6 pb-2">
                    <img src="../assets/logo.png" class="h-8 shadow-sm mb-6 opacity-90 transition hover:opacity-100" alt="Logo">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 ml-2">Menu</div>
                </div>
                <nav class="space-y-1 flex-1 px-4">
                    <button onclick="App.setTab('overview')" class="w-full text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-all ${state.activeTab === 'overview' ? 'nav-active bg-indigo-600/10 text-white' : 'hover:bg-slate-800 hover:text-white'}"><i class="fas fa-chart-pie w-5 text-center"></i> <span class="font-medium">Overview</span></button>
                    <button onclick="App.setTab('projects')" class="w-full text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-all ${state.activeTab === 'projects' ? 'nav-active bg-indigo-600/10 text-white' : 'hover:bg-slate-800 hover:text-white'}"><i class="fas fa-briefcase w-5 text-center"></i> <span class="font-medium">Projects</span></button>
                    <button onclick="App.setTab('clients')" class="w-full text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-all ${state.activeTab === 'clients' ? 'nav-active bg-indigo-600/10 text-white' : 'hover:bg-slate-800 hover:text-white'}"><i class="fas fa-users w-5 text-center"></i> <span class="font-medium">Clients</span></button>
                </nav>
                <div class="p-4 border-t border-slate-800">
                    <div class="bg-slate-800 rounded-xl p-3 flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 border-2 border-slate-700 flex items-center justify-center font-bold text-white text-xs shadow-md">AD</div>
                        <div class="truncate flex-1">
                            <div class="text-sm font-bold text-white truncate">${esc(state.admin.name || 'System Admin')}</div>
                            <div class="text-[10px] text-emerald-400 font-medium flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online</div>
                        </div>
                    </div>
                    <button onclick="App.doLogout()" class="w-full text-center px-4 py-2.5 rounded-lg text-sm text-rose-400 font-semibold hover:bg-rose-500/10 hover:text-rose-300 transition-colors"><i class="fas fa-sign-out-alt mr-1"></i> Terminate Session</button>
                </div>
            </aside>
            
            <!-- Mobile Header -->
            <div class="md:hidden bg-slate-900 border-b border-slate-800 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
                <img src="../assets/logo.png" style="filter: invert(1);" class="h-7" alt="Logo">
                <div class="flex gap-2">
                    <button onclick="App.setTab('overview')" class="w-10 h-10 rounded-lg flex items-center justify-center ${state.activeTab === 'overview' ? 'bg-indigo-600 text-white' : 'text-slate-400 bg-slate-800'}"><i class="fas fa-chart-pie"></i></button>
                    <button onclick="App.setTab('projects')" class="w-10 h-10 rounded-lg flex items-center justify-center ${state.activeTab === 'projects' ? 'bg-indigo-600 text-white' : 'text-slate-400 bg-slate-800'}"><i class="fas fa-briefcase"></i></button>
                    <button onclick="App.setTab('clients')" class="w-10 h-10 rounded-lg flex items-center justify-center ${state.activeTab === 'clients' ? 'bg-indigo-600 text-white' : 'text-slate-400 bg-slate-800'}"><i class="fas fa-users"></i></button>
                    <button onclick="App.doLogout()" class="w-10 h-10 rounded-lg text-rose-400 bg-slate-800 ml-1"><i class="fas fa-sign-out-alt"></i></button>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <main class="flex-1 md:ml-[260px] p-4 sm:p-8 md:p-10 lg:p-12 min-h-screen relative">
                <div class="absolute top-0 left-0 w-full h-64 bg-slate-900 -z-10 rounded-b-[3rem] hidden md:block"></div>
                <div id="tab-content" class="fade-in max-w-7xl mx-auto"></div>
            </main>
        </div>
    `,

    renderOverview: (state) => {
        const metrics = state.data.metrics;
        const totalProj = metrics.total_sites || state.data.projects.length;
        const totalClients = metrics.total_users || state.data.users.length;
        const recents = state.data.projects.slice(0, 6);
        const pending = state.data.projects.filter(p => p.status === 'pending').length;

        return `
            <div class="flex justify-between items-end mb-8 text-white">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Dashboard</h1>
                    <p class="text-slate-400 mt-1 font-medium">Agency operational overview</p>
                </div>
                <div class="hidden sm:block">
                    <span class="px-4 py-2 bg-white/10 backdrop-blur-md rounded-full text-sm font-semibold border border-white/20"><i class="fas fa-calendar-alt mr-2 text-indigo-400"></i>${new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric' })}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
                <div class="bg-white rounded-2xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center gap-5 hover:-translate-y-1 transition-transform">
                    <div class="w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-2xl shadow-inner"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <div class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-1">Total Projects</div>
                        <div class="text-3xl font-extrabold text-slate-800">${totalProj}</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center gap-5 hover:-translate-y-1 transition-transform">
                    <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 text-2xl shadow-inner"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-1">Active Clients</div>
                        <div class="text-3xl font-extrabold text-slate-800">${totalClients}</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center gap-5 hover:-translate-y-1 transition-transform">
                    <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 text-2xl shadow-inner"><i class="fas fa-inbox"></i></div>
                    <div>
                        <div class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-1">Pending Review</div>
                        <div class="text-3xl font-extrabold text-slate-800">${pending}</div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-xl shadow-slate-200/40 p-8 border border-slate-100">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-extrabold text-slate-900">Recent Projects Action Center</h2>
                        <button onclick="App.setTab('projects')" class="text-indigo-600 text-sm font-bold hover:underline">View All →</button>
                    </div>
                    <div class="space-y-4">
                        ${recents.map(p => `
                            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 hover:bg-slate-50 transition data-row group cursor-pointer" onclick="App.openModal('${p.id}')">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition"><i class="fas fa-laptop-code"></i></div>
                                    <div>
                                        <div class="font-bold text-slate-900">${esc(p.title)}</div>
                                        <div class="text-xs font-semibold text-slate-500">${esc(p.clientName)}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    ${getStatusBadge(p.status)}
                                    <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                                </div>
                            </div>
                        `).join('') || '<div class="text-center py-6 text-slate-500 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">No active projects to display</div>'}
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-900 rounded-[2rem] shadow-xl p-8 text-white flex flex-col relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-white opacity-5 rounded-full blur-2xl"></div>
                    <h2 class="text-xl font-extrabold mb-2 relative z-10"><i class="fas fa-bolt text-amber-300 mr-2"></i> Quick Actions</h2>
                    <p class="text-indigo-200 text-sm font-medium mb-8 relative z-10">Manage the portal rapidly.</p>
                    
                    <div class="space-y-3 relative z-10 flex-1">
                        <button onclick="App.setTab('clients')" class="w-full bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-md rounded-xl p-4 flex items-center justify-between transition group">
                            <span class="font-bold"><i class="fas fa-user-plus mr-2 opacity-70"></i> Client Directory</span>
                            <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition transform -translate-x-2 group-hover:translate-x-0"></i>
                        </button>
                        <a href="/index.html" target="_blank" class="w-full bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-md rounded-xl p-4 flex items-center justify-between transition group">
                            <span class="font-bold"><i class="fas fa-globe mr-2 opacity-70"></i> Visit Main Site</span>
                            <i class="fas fa-external-link-alt text-xs opacity-70"></i>
                        </a>
                    </div>
                </div>
            </div>
        `;
    },

    renderProjects: (state) => {
        return `
            <div class="mb-8 md:text-white">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Project Database</h1>
                <p class="text-slate-400 mt-1 font-medium md:text-indigo-200">Complete overview of all agency requests and builds.</p>
            </div>
            <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-200 overflow-hidden slide-in-right">
                <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Projects Directory</h3>
                    <div class="text-xs font-bold text-slate-500 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">${state.data.projects.length} Records</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-white text-xs uppercase text-slate-400 font-bold tracking-wider">
                            <tr>
                                <th class="p-5 border-b border-slate-100">Project / Client</th>
                                <th class="p-5 border-b border-slate-100">Tech & Budget</th>
                                <th class="p-5 border-b border-slate-100">Status</th>
                                <th class="p-5 border-b border-slate-100">Live Links</th>
                                <th class="p-5 border-b border-slate-100 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            ${state.data.projects.map(p => `
                                <tr class="data-row group">
                                    <td class="p-5">
                                        <div class="font-extrabold text-slate-900 text-base">${esc(p.title)}</div>
                                        <div class="text-xs font-semibold text-slate-500 mt-0.5 flex items-center gap-1.5"><i class="fas fa-user text-slate-300"></i> ${esc(p.clientName)}</div>
                                    </td>
                                    <td class="p-5">
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5"><i class="fas fa-code text-indigo-400"></i> ${esc(p.techStack) || 'N/A'}</div>
                                        <div class="text-xs font-semibold text-slate-500 mt-1">${esc(p.budget)}</div>
                                    </td>
                                    <td class="p-5">${getStatusBadge(p.status)}</td>
                                    <td class="p-5 text-sm">
                                        ${p.published_url ? `<a href="${esc(p.published_url)}" target="_blank" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded font-bold transition"><i class="fas fa-globe text-indigo-500"></i> Site</a>` : '<span class="text-slate-300 font-medium"><i class="fas fa-globe mr-1"></i>--</span>'}
                                        ${p.app_url ? `<a href="${esc(p.app_url)}" target="_blank" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded font-bold transition ml-2"><i class="fas fa-mobile-alt text-purple-500"></i> App</a>` : ''}
                                    </td>
                                    <td class="p-5 text-right">
                                        <button onclick="App.openModal(${p.id})" class="bg-white border-2 border-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs hover:border-indigo-500 hover:text-indigo-600 transition shadow-sm">
                                            Manage <i class="fas fa-cog ml-1"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('') || '<tr><td colspan="5" class="p-10 text-center text-slate-500 font-medium"><i class="fas fa-folder-open text-3xl mb-3 text-slate-300 block"></i> No projects found.</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    },

    renderClients: (state) => {
        return `
            <div class="mb-8 md:text-white">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Client Hub</h1>
                <p class="text-slate-400 mt-1 font-medium md:text-indigo-200">Registered portal users and contacts.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 slide-in-right">
                ${state.data.users.map(u => `
                    <div class="bg-white p-6 rounded-[2rem] shadow-lg shadow-slate-200/50 border border-slate-100 flex flex-col justify-between group hover:-translate-y-1 transition-all">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-100 to-indigo-50 flex items-center justify-center text-indigo-600 text-lg font-bold border border-indigo-200">${esc(u.name).charAt(0).toUpperCase()}</div>
                            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold uppercase px-2 py-1 rounded-full">${u.total_domains || 0} sites</span>
                        </div>
                        <div>
                            <div class="font-extrabold text-lg text-slate-900 group-hover:text-indigo-600 transition-colors">${esc(u.name)}</div>
                            <div class="text-sm font-semibold text-slate-500 mt-0.5"><i class="fas fa-envelope text-slate-400 mr-1.5"></i> ${esc(u.email)}</div>
                        </div>
                        <div class="mt-5 pt-4 border-t border-slate-100">
                            <a href="mailto:${esc(u.email)}" class="text-indigo-600 font-bold text-sm flex items-center gap-2 hover:underline"><i class="fas fa-paper-plane"></i> Contact Client</a>
                        </div>
                    </div>
                `).join('') || '<div class="col-span-full text-center p-12 bg-white rounded-3xl border border-slate-200 text-slate-500">No clients registered yet.</div>'}
            </div>
        `;
    },

    renderModal: (p) => `
        <div class="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4 fade-in">
            <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col max-h-[90vh]">
                <div class="bg-gradient-to-r from-slate-900 to-indigo-900 text-white px-8 py-6 flex justify-between items-center shrink-0 shadow-md">
                    <div>
                        <div class="text-indigo-300 text-[10px] uppercase font-bold tracking-widest mb-1">Editing Record</div>
                        <h3 class="font-extrabold text-xl truncate pr-4">${esc(p.title)}</h3>
                    </div>
                    <button onclick="App.closeModal()" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center text-slate-300 hover:bg-white/20 hover:text-white transition"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-8 overflow-y-auto space-y-6 bg-slate-50">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-3"><i class="fas fa-signal mr-1.5 text-indigo-400"></i> Project Status</label>
                        <select id="mod-status" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 bg-white focus:border-indigo-500 focus:ring-0 appearance-none">
                            <option value="pending" ${p.status === 'pending' ? 'selected' : ''}>⏳ Pending Review</option>
                            <option value="approved" ${p.status === 'approved' ? 'selected' : ''}>✅ Approved & Queued</option>
                            <option value="construction" ${p.status === 'construction' ? 'selected' : ''}>🔨 In Construction</option>
                        </select>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest"><i class="fas fa-link mr-1.5 text-indigo-400"></i> Output Links</label>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1.5 ml-1">Published Website URL</label>
                            <div class="relative">
                                <i class="fas fa-globe absolute left-4 top-3 text-slate-300"></i>
                                <input id="mod-pub" type="url" value="${esc(p.published_url)}" class="w-full border-2 border-slate-200 rounded-xl pl-11 pr-4 py-2.5 text-sm font-medium bg-slate-50 focus:bg-white focus:border-indigo-500" placeholder="https://...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1.5 ml-1">App Sign-in URL / Preview</label>
                            <div class="relative">
                                <i class="fas fa-mobile-alt absolute left-4 top-3 text-slate-300"></i>
                                <input id="mod-app" type="url" value="${esc(p.app_url)}" class="w-full border-2 border-slate-200 rounded-xl pl-11 pr-4 py-2.5 text-sm font-medium bg-slate-50 focus:bg-white focus:border-indigo-500" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1"><i class="fas fa-clipboard-list mr-1.5 text-indigo-400"></i> Admin Feedback Notes</label>
                        <textarea id="mod-notes" rows="3" class="w-full border-2 border-slate-200 rounded-2xl px-4 py-3 text-sm font-medium bg-white focus:bg-white focus:border-indigo-500 shadow-sm" placeholder="Add feedback visible to the client...">${esc(p.admin_notes)}</textarea>
                    </div>
                </div>
                <div class="p-6 bg-white border-t border-slate-200 shrink-0 flex gap-4">
                    <button onclick="App.closeModal()" class="flex-1 bg-slate-100 text-slate-600 font-bold py-3.5 rounded-xl hover:bg-slate-200 transition">Cancel</button>
                    <button onclick="App.saveProject(${p.id})" class="flex-1 bg-indigo-600 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-600/30 flex justify-center items-center gap-2">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    `
};

window.Views = Views;
