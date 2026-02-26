/**
 * Frontend Admin Management
 * UI for managing menus, pages, and users from the frontend
 */

class FrontendAdmin {
    constructor() {
        this.init();
    }

    init() {
        this.createAdminButton();
        this.setupEventListeners();
    }

    createAdminButton() {
        const adminBtn = document.createElement('div');
        adminBtn.id = 'frontend-admin-toggle';
        adminBtn.innerHTML = `
            <button id="admin-panel-btn" class="admin-toggle-btn" title="Site Management">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6M5.6 5.6l4.2 4.2m4.2 4.2l4.2 4.2M1 12h6m6 0h6M5.6 18.4l4.2-4.2m4.2-4.2l4.2-4.2"></path>
                </svg>
                <span>Admin</span>
            </button>
            <div id="admin-panel" class="admin-panel" style="display: none;">
                <div class="admin-panel-header">
                    <h3>Site Management</h3>
                    <button id="close-admin-panel-btn">&times;</button>
                </div>
                <div class="admin-panel-tabs">
                    <button class="admin-tab active" data-tab="menus">📋 Menus</button>
                    <button class="admin-tab" data-tab="pages">📄 Pages</button>
                    <button class="admin-tab" data-tab="users">👥 Users</button>
                    <button class="admin-tab" data-tab="themes">🎨 Themes</button>
                    <button class="admin-tab" data-tab="appearance">⚙️ Appearance</button>
                </div>
                <div class="admin-panel-content">
                    ${this.getMenusHTML()}
                    ${this.getPagesHTML()}
                    ${this.getUsersHTML()}
                    ${this.getThemesHTML()}
                    ${this.getAppearanceHTML()}
                </div>
            </div>
        `;
        document.body.appendChild(adminBtn);
    }

    getMenusHTML() {
        return `
            <div id="admin-tab-menus" class="admin-tab-content active">
                <h4>Menu Management</h4>
                <div class="admin-section">
                    <h5>Add New Menu Item</h5>
                    <div class="form-group">
                        <label>Menu ID (slug):</label>
                        <input type="text" id="menu-id" placeholder="e.g., blog">
                    </div>
                    <div class="form-group">
                        <label>Label:</label>
                        <input type="text" id="menu-label" placeholder="e.g., Blog">
                    </div>
                    <div class="form-group">
                        <label>Type:</label>
                        <select id="menu-type">
                            <option value="page">Page</option>
                            <option value="link">External Link</option>
                        </select>
                    </div>
                    <div class="form-group" id="menu-url-group" style="display: none;">
                        <label>URL:</label>
                        <input type="url" id="menu-url" placeholder="https://example.com">
                    </div>
                    <button id="add-menu-btn" class="btn-primary">Add Menu Item</button>
                </div>
                <div class="admin-section">
                    <h5>Existing Menu Items</h5>
                    <div id="menus-list">Loading...</div>
                </div>
            </div>
        `;
    }

    getPagesHTML() {
        return `
            <div id="admin-tab-pages" class="admin-tab-content">
                <h4>Page Management</h4>
                <div class="admin-section">
                    <h5>Create New Page</h5>
                    <div class="form-group">
                        <label>Page ID (slug):</label>
                        <input type="text" id="page-id" placeholder="e.g., blog">
                    </div>
                    <div class="form-group">
                        <label>Page Title:</label>
                        <input type="text" id="page-title" placeholder="e.g., Blog">
                    </div>
                    <button id="add-page-btn" class="btn-primary">Create Page</button>
                </div>
                <div class="admin-section">
                    <h5>Existing Pages</h5>
                    <p class="info-text">Pages can be edited inline on the frontend using Edit Mode.</p>
                    <div id="pages-list">Loading...</div>
                </div>
            </div>
        `;
    }

    getUsersHTML() {
        return `
            <div id="admin-tab-users" class="admin-tab-content">
                <h4>User Management</h4>
                <div class="admin-section">
                    <h5>Add New User</h5>
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" id="user-username" placeholder="username">
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" id="user-password" placeholder="minimum 6 characters">
                    </div>
                    <button id="add-user-btn" class="btn-primary">Add User</button>
                </div>
                <div class="admin-section">
                    <h5>Existing Users</h5>
                    <div id="users-list">Loading...</div>
                </div>
            </div>
        `;
    }

    getThemesHTML() {
        return `
            <div id="admin-tab-themes" class="admin-tab-content">
                <h4>Website Themes</h4>
                <p class="info-text">Select a theme to customize your website appearance.</p>
                <div class="admin-section">
                    <h5>Create New Theme</h5>
                    <div class="theme-form-grid">
                        <div class="form-group">
                            <label>Theme Name:</label>
                            <input type="text" id="theme-name" placeholder="e.g., Sunrise">
                        </div>
                        <div class="form-group">
                            <label>Theme ID (slug):</label>
                            <input type="text" id="theme-id" placeholder="e.g., sunrise">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <input type="text" id="theme-description" placeholder="Short description">
                    </div>
                    <div class="theme-form-grid">
                        <div class="form-group">
                            <label>Background:</label>
                            <input type="color" id="theme-color-background" value="#FFFFFF">
                        </div>
                        <div class="form-group">
                            <label>Text:</label>
                            <input type="color" id="theme-color-text" value="#1F2937">
                        </div>
                        <div class="form-group">
                            <label>Primary:</label>
                            <input type="color" id="theme-color-primary" value="#3B82F6">
                        </div>
                        <div class="form-group">
                            <label>Secondary:</label>
                            <input type="color" id="theme-color-secondary" value="#F3F4F6">
                        </div>
                        <div class="form-group">
                            <label>Accent:</label>
                            <input type="color" id="theme-color-accent" value="#111827">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" id="theme-enabled" checked>
                            <span>Enable theme after creation</span>
                        </label>
                    </div>
                    <button id="create-theme-btn" class="btn-primary">Create Theme</button>
                </div>
                <div class="admin-section">
                    <h5>Available Themes</h5>
                    <div id="themes-list" class="themes-grid">Loading themes...</div>
                </div>
                <div class="admin-section">
                    <h5>Active Theme</h5>
                    <div id="active-theme-info" style="padding: 15px; background: #f0f9ff; border-left: 4px solid #3B82F6; border-radius: 4px;">
                        <strong>Current Theme:</strong> <span id="current-theme-name">Loading...</span>
                    </div>
                </div>
            </div>
        `;
    }

    getAppearanceHTML() {
        return `
            <div id="admin-tab-appearance" class="admin-tab-content">
                <h4>Appearance Settings</h4>
                <div class="admin-section">
                    <h5>Frontend Features</h5>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" id="search-enabled" checked>
                            <span>Enable Search Box</span>
                            <small style="color: #666; margin-left: auto;">(Shows search box in sidebar)</small>
                        </label>
                    </div>
                    <button id="save-appearance-btn" class="btn-primary">Save Settings</button>
                    <div id="appearance-message" style="margin-top: 10px;"></div>
                </div>
            </div>
        `;
    }

    setupEventListeners() {
        // Toggle panel
        document.getElementById('admin-panel-btn').addEventListener('click', () => {
            const panel = document.getElementById('admin-panel');
            const isVisible = panel.style.display !== 'none';
            panel.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) {
                this.loadMenus();
                this.loadPages();
                this.loadUsers();
                this.loadThemes();
                this.loadAppearance();
            }
        });

        // Close panel
        document.getElementById('close-admin-panel-btn').addEventListener('click', () => {
            document.getElementById('admin-panel').style.display = 'none';
        });

        // Tab switching
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.getAttribute('data-tab');
                this.switchTab(tabName);
            });
        });

        // Menu type change
        document.getElementById('menu-type').addEventListener('change', (e) => {
            const urlGroup = document.getElementById('menu-url-group');
            urlGroup.style.display = e.target.value === 'link' ? 'block' : 'none';
        });

        // Add menu
        document.getElementById('add-menu-btn').addEventListener('click', () => this.addMenu());

        // Add page
        document.getElementById('add-page-btn').addEventListener('click', () => this.addPage());

        // Add user
        document.getElementById('add-user-btn').addEventListener('click', () => this.addUser());

        // Save appearance settings
        document.getElementById('save-appearance-btn').addEventListener('click', () => this.saveAppearance());

        // Create theme
        const createThemeBtn = document.getElementById('create-theme-btn');
        if (createThemeBtn) {
            createThemeBtn.addEventListener('click', () => this.createTheme());
        }
    }

    switchTab(tabName) {
        document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.admin-tab-content').forEach(c => c.classList.remove('active'));
        
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`admin-tab-${tabName}`).classList.add('active');

        // Load data when switching tabs
        if (tabName === 'menus') this.loadMenus();
        if (tabName === 'pages') this.loadPages();
        if (tabName === 'users') this.loadUsers();
        if (tabName === 'themes') this.loadThemes();
        if (tabName === 'appearance') this.loadAppearance();
    }

    async loadMenus() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_menus');
            
            const response = await fetch('frontend-admin-menus.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.renderMenus(result.menus);
            } else {
                this.showNotification('Error loading menus: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to load menus: ' + error.message, 'error');
        }
    }

    renderMenus(menus) {
        const list = document.getElementById('menus-list');
        if (menus.length === 0) {
            list.innerHTML = '<p class="empty-state">No menu items yet.</p>';
            return;
        }

        list.innerHTML = menus.map(menu => `
            <div class="admin-item">
                <div class="admin-item-info">
                    <strong>${menu.label}</strong>
                    <span class="admin-item-meta">${menu.type} • ${menu.id}${menu.url ? ' • ' + menu.url : ''}</span>
                </div>
                <div class="admin-item-actions">
                    <button class="btn-sm btn-danger" onclick="frontendAdmin.deleteMenu('${menu.id}')">Delete</button>
                </div>
            </div>
        `).join('');
    }

    async addMenu() {
        const id = document.getElementById('menu-id').value.trim();
        const label = document.getElementById('menu-label').value.trim();
        const type = document.getElementById('menu-type').value;
        const url = document.getElementById('menu-url').value.trim();

        if (!id || !label) {
            this.showNotification('Please fill in ID and label', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'add_menu');
            formData.append('id', id);
            formData.append('label', label);
            formData.append('type', type);
            if (type === 'link') formData.append('url', url);

            const response = await fetch('frontend-admin-menus.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Menu item added successfully!', 'success');
                document.getElementById('menu-id').value = '';
                document.getElementById('menu-label').value = '';
                document.getElementById('menu-url').value = '';
                this.loadMenus();
                // Reload page to update nav
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to add menu: ' + error.message, 'error');
        }
    }

    async deleteMenu(id) {
        if (!confirm(`Delete menu item "${id}"?`)) return;

        try {
            const formData = new FormData();
            formData.append('action', 'delete_menu');
            formData.append('id', id);

            const response = await fetch('frontend-admin-menus.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Menu item deleted', 'success');
                this.loadMenus();
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to delete menu: ' + error.message, 'error');
        }
    }

    async loadPages() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_pages');
            
            const response = await fetch('frontend-admin-pages.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.renderPages(result.pages);
            } else {
                this.showNotification('Error loading pages: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to load pages: ' + error.message, 'error');
        }
    }

    renderPages(pages) {
        const list = document.getElementById('pages-list');
        const pageIds = Object.keys(pages);
        
        if (pageIds.length === 0) {
            list.innerHTML = '<p class="empty-state">No pages yet.</p>';
            return;
        }

        list.innerHTML = pageIds.map(id => {
            const page = pages[id];
            const isProtected = ['home', 'about', 'services', 'contact'].includes(id);
            return `
                <div class="admin-item">
                    <div class="admin-item-info">
                        <strong>${page.title || id}</strong>
                        <span class="admin-item-meta">${id}${isProtected ? ' • Protected' : ''}</span>
                    </div>
                    <div class="admin-item-actions">
                        <a href="?page=${id}" class="btn-sm btn-secondary">View</a>
                        ${!isProtected ? `<button class="btn-sm btn-danger" onclick="frontendAdmin.deletePage('${id}')">Delete</button>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    async addPage() {
        const id = document.getElementById('page-id').value.trim();
        const title = document.getElementById('page-title').value.trim();

        if (!id || !title) {
            this.showNotification('Please fill in page ID and title', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'add_page');
            formData.append('id', id);
            formData.append('title', title);

            const response = await fetch('frontend-admin-pages.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Page created successfully!', 'success');
                document.getElementById('page-id').value = '';
                document.getElementById('page-title').value = '';
                this.loadPages();
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to create page: ' + error.message, 'error');
        }
    }

    async deletePage(id) {
        if (!confirm(`Delete page "${id}"? This cannot be undone.`)) return;

        try {
            const formData = new FormData();
            formData.append('action', 'delete_page');
            formData.append('id', id);

            const response = await fetch('frontend-admin-pages.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Page deleted', 'success');
                this.loadPages();
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to delete page: ' + error.message, 'error');
        }
    }

    async loadUsers() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_users');
            
            const response = await fetch('frontend-admin-users.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.renderUsers(result.users);
            } else {
                this.showNotification('Error loading users: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to load users: ' + error.message, 'error');
        }
    }

    renderUsers(users) {
        const list = document.getElementById('users-list');
        
        if (users.length === 0) {
            list.innerHTML = '<p class="empty-state">No users yet.</p>';
            return;
        }

        list.innerHTML = users.map(username => `
            <div class="admin-item">
                <div class="admin-item-info">
                    <strong>${username}</strong>
                </div>
                <div class="admin-item-actions">
                    <button class="btn-sm btn-secondary" onclick="frontendAdmin.changePassword('${username}')">Change Password</button>
                    <button class="btn-sm btn-danger" onclick="frontendAdmin.deleteUser('${username}')">Delete</button>
                </div>
            </div>
        `).join('');
    }

    async addUser() {
        const username = document.getElementById('user-username').value.trim();
        const password = document.getElementById('user-password').value.trim();

        if (!username || !password) {
            this.showNotification('Please fill in username and password', 'error');
            return;
        }

        if (password.length < 6) {
            this.showNotification('Password must be at least 6 characters', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'add_user');
            formData.append('username', username);
            formData.append('password', password);

            const response = await fetch('frontend-admin-users.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('User added successfully!', 'success');
                document.getElementById('user-username').value = '';
                document.getElementById('user-password').value = '';
                this.loadUsers();
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to add user: ' + error.message, 'error');
        }
    }

    async changePassword(username) {
        const newPassword = prompt(`Enter new password for ${username} (min 6 characters):`);
        if (!newPassword) return;

        if (newPassword.length < 6) {
            this.showNotification('Password must be at least 6 characters', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'change_password');
            formData.append('username', username);
            formData.append('password', newPassword);

            const response = await fetch('frontend-admin-users.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Password changed successfully!', 'success');
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to change password: ' + error.message, 'error');
        }
    }

    async deleteUser(username) {
        if (!confirm(`Delete user "${username}"? This cannot be undone.`)) return;

        try {
            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('username', username);

            const response = await fetch('frontend-admin-users.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('User deleted', 'success');
                this.loadUsers();
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to delete user: ' + error.message, 'error');
        }
    }

    async loadAppearance() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_settings');

            const response = await fetch('frontend-admin-appearance.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('search-enabled').checked = result.design.search_enabled || true;
            } else {
                this.showNotification('Error loading settings: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to load settings: ' + error.message, 'error');
        }
    }

    async saveAppearance() {
        try {
            const searchEnabled = document.getElementById('search-enabled').checked;
            const formData = new FormData();
            formData.append('action', 'toggle_search');
            formData.append('enabled', searchEnabled ? 1 : 0);

            const response = await fetch('frontend-admin-appearance.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Settings saved! Reload the page to see changes.', 'success');
                // Optionally reload the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to save settings: ' + error.message, 'error');
        }
    }

    async loadThemes() {
        try {
            const response = await fetch('frontend-admin-themes.php?action=get_themes');
            const result = await response.json();

            if (result.success) {
                this.renderThemes(result.themes, result.active_theme);
            } else {
                this.showNotification('Error loading themes: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to load themes: ' + error.message, 'error');
        }
    }

    renderThemes(themes, activeTheme) {
        const list = document.getElementById('themes-list');
        const currentThemeName = document.getElementById('current-theme-name');

        if (!themes || themes.length === 0) {
            list.innerHTML = '<p class="empty-state">No themes available.</p>';
            return;
        }

        currentThemeName.textContent = activeTheme || 'Default';

        const orderedThemes = [...themes].sort((a, b) => {
            if (a.id === activeTheme) return -1;
            if (b.id === activeTheme) return 1;
            if (a.enabled === b.enabled) return a.name.localeCompare(b.name);
            return a.enabled ? -1 : 1;
        });

        const previewVariants = ['preview-layout-a', 'preview-layout-b', 'preview-layout-c'];

        list.innerHTML = orderedThemes.map((theme, index) => {
            const variantClass = previewVariants[index % previewVariants.length];
            const previewStyle = `--preview-bg: ${theme.colors.background}; --preview-text: ${theme.colors.text}; --preview-primary: ${theme.colors.primary}; --preview-secondary: ${theme.colors.secondary}; --preview-accent: ${theme.colors.accent}; --preview-border: ${theme.colors.border || '#ddd'};`;

            return `
            <div class="theme-card ${theme.id === activeTheme ? 'theme-active' : ''} ${theme.enabled ? '' : 'theme-disabled'}">
                <div class="theme-preview ${variantClass}" style="${previewStyle}">
                    <div class="theme-preview-bar"></div>
                    <div class="theme-preview-body">
                        <div class="theme-preview-chip">Menu</div>
                        <div class="theme-preview-title">Hero Title</div>
                        <div class="theme-preview-line"></div>
                        <div class="theme-preview-actions">
                            <span class="theme-preview-btn">Primary</span>
                            <span class="theme-preview-btn outline">Ghost</span>
                        </div>
                    </div>
                    <div class="theme-preview-card">
                        <div class="theme-preview-card-line"></div>
                        <div class="theme-preview-card-line short"></div>
                    </div>
                </div>
                <div class="theme-info">
                    <h5>${theme.name}</h5>
                    <p>${theme.description}</p>
                    <div class="theme-status">${theme.enabled ? 'Enabled' : 'Disabled'}</div>
                    <div class="theme-colors">
                        <span class="color-dot" style="background-color: ${theme.colors.primary};" title="Primary"></span>
                        <span class="color-dot" style="background-color: ${theme.colors.secondary};" title="Secondary"></span>
                        <span class="color-dot" style="background-color: ${theme.colors.accent};" title="Accent"></span>
                    </div>
                </div>
                <div class="theme-actions">
                    <button class="btn-sm ${theme.id === activeTheme ? 'btn-secondary' : 'btn-primary'}" 
                            onclick="frontendAdmin.selectTheme('${theme.id}')"
                            ${theme.id === activeTheme || !theme.enabled ? 'disabled' : ''}>
                        ${theme.id === activeTheme ? '✓ Active' : 'Activate'}
                    </button>
                    <button class="btn-sm btn-secondary" 
                            onclick="frontendAdmin.toggleTheme('${theme.id}', ${theme.enabled ? 'false' : 'true'})">
                        ${theme.enabled ? 'Disable' : 'Enable'}
                    </button>
                </div>
            </div>
        `;
        }).join('');
    }

    async selectTheme(themeId) {
        try {
            const formData = new FormData();
            formData.append('action', 'set_theme');
            formData.append('theme_id', themeId);

            const response = await fetch('frontend-admin-themes.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Theme activated! Reloading...', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to change theme: ' + error.message, 'error');
        }
    }

    async toggleTheme(themeId, enabled) {
        try {
            const formData = new FormData();
            formData.append('action', 'toggle_theme');
            formData.append('theme_id', themeId);
            formData.append('enabled', enabled ? 1 : 0);

            const response = await fetch('frontend-admin-themes.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message || 'Theme updated', 'success');
                this.loadThemes();
                if (result.active_theme) {
                    document.getElementById('current-theme-name').textContent = result.active_theme;
                }
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update theme: ' + error.message, 'error');
        }
    }

    async createTheme() {
        const name = document.getElementById('theme-name').value.trim();
        const id = document.getElementById('theme-id').value.trim();
        const description = document.getElementById('theme-description').value.trim();
        const enabled = document.getElementById('theme-enabled').checked;

        if (!name) {
            this.showNotification('Please provide a theme name', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'create_theme');
            formData.append('theme_name', name);
            formData.append('theme_id', id);
            formData.append('theme_description', description);
            formData.append('color_background', document.getElementById('theme-color-background').value);
            formData.append('color_text', document.getElementById('theme-color-text').value);
            formData.append('color_primary', document.getElementById('theme-color-primary').value);
            formData.append('color_secondary', document.getElementById('theme-color-secondary').value);
            formData.append('color_accent', document.getElementById('theme-color-accent').value);
            formData.append('enabled', enabled ? 1 : 0);

            const response = await fetch('frontend-admin-themes.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Theme created successfully', 'success');
                document.getElementById('theme-name').value = '';
                document.getElementById('theme-id').value = '';
                document.getElementById('theme-description').value = '';
                this.loadThemes();
            } else {
                this.showNotification('Error: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to create theme: ' + error.message, 'error');
        }
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `inline-edit-notification notification-${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialize frontend admin when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.isAdminLoggedIn) {
        window.frontendAdmin = new FrontendAdmin();
    }
});
