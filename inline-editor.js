/**
 * SimplePHP Inline Editor
 * Allows inline editing of content when admin is logged in
 */

class InlineEditor {
    constructor() {
        this.editMode = false;
        this.originalContent = {};
        this.contentData = {};
        this.editableElements = [];
        this.init();
    }

    init() {
        this.loadContent();
        this.createEditToggle();
        this.setupEventListeners();
        this.restoreEditMode();
    }

    restoreEditMode() {
        // Check if edit mode was previously enabled
        const wasEditModeOn = sessionStorage.getItem('inlineEditMode') === 'true';
        if (wasEditModeOn) {
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                this.enableEditMode();
            }, 100);
        }
    }

    enableEditMode() {
        if (this.editMode) return; // Already enabled
        this.editMode = true;
        sessionStorage.setItem('inlineEditMode', 'true');
        this.updateEditModeUI();
    }

    disableEditMode() {
        if (!this.editMode) return; // Already disabled
        this.editMode = false;
        sessionStorage.setItem('inlineEditMode', 'false');
        this.updateEditModeUI();
    }

    async loadContent() {
        try {
            const response = await fetch('data/content.json');
            this.contentData = await response.json();
        } catch (error) {
            console.error('Failed to load content:', error);
        }
    }

    createEditToggle() {
        // Create floating edit mode toggle button
        const toggle = document.createElement('div');
        toggle.id = 'inline-edit-toggle';
        toggle.innerHTML = `
            <button id="edit-mode-btn" class="edit-toggle-btn" title="Toggle Edit Mode">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Edit Mode</span>
            </button>
            <div id="edit-mode-panel" class="edit-panel" style="display: none;">
                <div class="edit-panel-header">
                    <h3>Inline Editor</h3>
                    <button id="close-panel-btn">&times;</button>
                </div>
                <div class="edit-panel-content">
                    <div class="edit-status">Edit mode: <strong id="edit-status">OFF</strong></div>
                    <button id="save-all-btn" class="btn-primary" style="display: none;">💾 Save All Changes</button>
                    <button id="cancel-all-btn" class="btn-secondary" style="display: none;">↶ Cancel All</button>
                    <hr>
                    <div id="edit-instructions">
                        <p><strong>How to use:</strong></p>
                        <ul>
                            <li>Click "Start Editing" to begin</li>
                            <li>Click any editable element to modify</li>
                            <li>Double-click to add new sections</li>
                            <li>Use the toolbar for formatting</li>
                            <li>Click "Done Editing" to finish</li>
                        </ul>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            <em>Note: Edit mode stays active when you navigate to other pages.</em>
                        </p>
                    </div>
                    <div id="changes-count" style="display: none;">
                        Changes made: <strong id="change-counter">0</strong>
                    </div>
                </div>
                <button id="toggle-edit-btn" class="btn-toggle">Start Editing</button>
            </div>
        `;
        document.body.appendChild(toggle);
    }

    setupEventListeners() {
        // Toggle panel visibility
        document.getElementById('edit-mode-btn').addEventListener('click', () => {
            const panel = document.getElementById('edit-mode-panel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        });

        // Close panel
        document.getElementById('close-panel-btn').addEventListener('click', () => {
            document.getElementById('edit-mode-panel').style.display = 'none';
        });

        // Toggle edit mode
        document.getElementById('toggle-edit-btn').addEventListener('click', () => {
            if (this.editMode) {
                this.disableEditMode();
            } else {
                this.enableEditMode();
            }
        });

        // Save all changes
        document.getElementById('save-all-btn').addEventListener('click', () => {
            this.saveAllChanges();
        });

        // Cancel all changes
        document.getElementById('cancel-all-btn').addEventListener('click', () => {
            this.cancelAllChanges();
        });

        // Handle clicks on editable elements when in edit mode
        document.addEventListener('click', (e) => {
            if (!this.editMode) return;
            
            // Find the closest editable element
            const editable = e.target.closest('[data-editable]');
            if (editable && !editable.classList.contains('editing')) {
                e.preventDefault();
                e.stopPropagation();
                this.makeElementEditable(editable);
            }
        });

        // Handle double-click to add new elements
        document.addEventListener('dblclick', (e) => {
            if (!this.editMode) return;
            
            const container = e.target.closest('[data-editable-container]');
            if (container) {
                this.addNewElement(container, e);
            }
        });
    }

    updateEditModeUI() {
        const btn = document.getElementById('toggle-edit-btn');
        const status = document.getElementById('edit-status');
        const saveBtn = document.getElementById('save-all-btn');
        const cancelBtn = document.getElementById('cancel-all-btn');
        const instructions = document.getElementById('edit-instructions');
        const changesCount = document.getElementById('changes-count');

        if (this.editMode) {
            btn.textContent = 'Done Editing';
            btn.classList.add('active');
            status.textContent = 'ON';
            status.style.color = '#10b981';
            saveBtn.style.display = 'block';
            cancelBtn.style.display = 'block';
            instructions.style.display = 'none';
            changesCount.style.display = 'block';
            document.body.classList.add('inline-edit-mode');
            this.markEditableElements();
            this.showNotification('Edit mode enabled. Click elements to edit. Stays active across pages.', 'success');
        } else {
            btn.textContent = 'Start Editing';
            btn.classList.remove('active');
            status.textContent = 'OFF';
            status.style.color = '#ef4444';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            instructions.style.display = 'block';
            changesCount.style.display = 'none';
            document.body.classList.remove('inline-edit-mode');
            this.cleanupEditableElements();
            this.showNotification('Edit mode disabled.', 'info');
        }
    }

    markEditableElements() {
        // Mark all elements with data-editable attribute
        const elements = document.querySelectorAll('[data-editable]');
        elements.forEach(el => {
            el.classList.add('editable-highlight');
            el.setAttribute('title', 'Click to edit');
        });

        // Mark containers where new elements can be added
        const containers = document.querySelectorAll('[data-editable-container]');
        containers.forEach(el => {
            el.classList.add('editable-container-highlight');
            el.setAttribute('title', 'Double-click to add new content');
        });
    }

    cleanupEditableElements() {
        // Remove all editing highlights and states
        document.querySelectorAll('.editable-highlight').forEach(el => {
            el.classList.remove('editable-highlight');
            el.removeAttribute('title');
        });

        document.querySelectorAll('.editable-container-highlight').forEach(el => {
            el.classList.remove('editable-container-highlight');
            el.removeAttribute('title');
        });

        document.querySelectorAll('.editing').forEach(el => {
            this.cancelEdit(el);
        });
    }

    makeElementEditable(element) {
        // Store original content
        const key = element.getAttribute('data-editable');
        if (!this.originalContent[key]) {
            this.originalContent[key] = element.innerHTML;
        }

        element.classList.add('editing');
        element.contentEditable = true;
        element.focus();

        // Create editing toolbar
        const toolbar = this.createEditToolbar(element);
        element.parentNode.insertBefore(toolbar, element);

        // Select all text
        const range = document.createRange();
        range.selectNodeContents(element);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }

    createEditToolbar(element) {
        const toolbar = document.createElement('div');
        toolbar.className = 'edit-toolbar';
        toolbar.innerHTML = `
            <div class="edit-toolbar-buttons">
                <button class="toolbar-btn" data-command="bold" title="Bold"><strong>B</strong></button>
                <button class="toolbar-btn" data-command="italic" title="Italic"><em>I</em></button>
                <button class="toolbar-btn" data-command="underline" title="Underline"><u>U</u></button>
                <span class="toolbar-separator">|</span>
                <button class="toolbar-btn" data-command="formatBlock:h1" title="Heading 1">H1</button>
                <button class="toolbar-btn" data-command="formatBlock:h2" title="Heading 2">H2</button>
                <button class="toolbar-btn" data-command="formatBlock:p" title="Paragraph">P</button>
                <span class="toolbar-separator">|</span>
                <button class="toolbar-btn toolbar-btn-save" data-action="save" title="Save">✓ Save</button>
                <button class="toolbar-btn toolbar-btn-cancel" data-action="cancel" title="Cancel">✗ Cancel</button>
            </div>
        `;

        // Toolbar button handlers
        toolbar.querySelectorAll('[data-command]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const command = btn.getAttribute('data-command').split(':');
                if (command.length === 2) {
                    document.execCommand(command[0], false, command[1]);
                } else {
                    document.execCommand(command[0], false, null);
                }
                element.focus();
            });
        });

        toolbar.querySelector('[data-action="save"]').addEventListener('click', (e) => {
            e.preventDefault();
            this.saveEdit(element);
        });

        toolbar.querySelector('[data-action="cancel"]').addEventListener('click', (e) => {
            e.preventDefault();
            this.cancelEdit(element);
        });

        return toolbar;
    }

    async saveEdit(element) {
        const key = element.getAttribute('data-editable');
        const newContent = element.innerHTML;

        // Update content data
        this.setContentValue(key, newContent);

        // Clean up
        element.contentEditable = false;
        element.classList.remove('editing');
        element.classList.add('edited');

        // Remove toolbar
        const toolbar = element.previousElementSibling;
        if (toolbar && toolbar.classList.contains('edit-toolbar')) {
            toolbar.remove();
        }

        this.updateChangeCounter();

        // Save to server immediately
        try {
            const response = await fetch('save-inline-edit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.contentData)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('✓ Change saved successfully!', 'success');
                element.classList.remove('edited');
                this.updateChangeCounter();
            } else {
                this.showNotification('Error saving changes: ' + result.error, 'error');
                element.classList.add('edited');
            }
        } catch (error) {
            this.showNotification('Failed to save changes: ' + error.message, 'error');
            element.classList.add('edited');
        }
    }

    cancelEdit(element) {
        const key = element.getAttribute('data-editable');
        
        // Restore original content
        if (this.originalContent[key]) {
            element.innerHTML = this.originalContent[key];
        }

        element.contentEditable = false;
        element.classList.remove('editing');

        // Remove toolbar
        const toolbar = element.previousElementSibling;
        if (toolbar && toolbar.classList.contains('edit-toolbar')) {
            toolbar.remove();
        }
    }

    addNewElement(container, event) {
        event.preventDefault();
        const containerType = container.getAttribute('data-editable-container');
        
        const newElement = document.createElement('div');
        newElement.setAttribute('data-editable', `new-${Date.now()}`);
        newElement.className = 'new-editable-element';
        newElement.innerHTML = '<p>New content - click to edit</p>';
        
        container.appendChild(newElement);
        this.markEditableElements();
        this.makeElementEditable(newElement);
        
        this.showNotification('New element added. Edit and save when ready.', 'success');
    }

    setContentValue(key, value) {
        const keys = key.split('.');
        let obj = this.contentData;
        
        for (let i = 0; i < keys.length - 1; i++) {
            if (!obj[keys[i]]) obj[keys[i]] = {};
            obj = obj[keys[i]];
        }
        
        obj[keys[keys.length - 1]] = value;
    }

    updateChangeCounter() {
        const editedCount = document.querySelectorAll('.edited').length;
        document.getElementById('change-counter').textContent = editedCount;
    }

    async saveAllChanges() {
        try {
            const response = await fetch('save-inline-edit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.contentData)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('All changes saved successfully!', 'success');
                this.originalContent = {};
                document.querySelectorAll('.edited').forEach(el => {
                    el.classList.remove('edited');
                });
                this.updateChangeCounter();
            } else {
                this.showNotification('Error saving changes: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to save changes: ' + error.message, 'error');
        }
    }

    cancelAllChanges() {
        if (!confirm('Are you sure you want to cancel all changes?')) {
            return;
        }

        // Restore all original content
        document.querySelectorAll('.edited, .editing').forEach(el => {
            const key = el.getAttribute('data-editable');
            if (this.originalContent[key]) {
                el.innerHTML = this.originalContent[key];
            }
            el.classList.remove('edited', 'editing');
            el.contentEditable = false;
        });

        // Remove all toolbars
        document.querySelectorAll('.edit-toolbar').forEach(toolbar => {
            toolbar.remove();
        });

        // Remove new elements
        document.querySelectorAll('.new-editable-element').forEach(el => {
            el.remove();
        });

        this.originalContent = {};
        this.loadContent(); // Reload original content
        this.updateChangeCounter();
        this.showNotification('All changes cancelled.', 'info');
    }

    showNotification(message, type = 'info') {
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

// Initialize inline editor when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.isAdminLoggedIn) {
        window.inlineEditor = new InlineEditor();
    }
});
