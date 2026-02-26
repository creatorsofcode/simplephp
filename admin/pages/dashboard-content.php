<div class="container-fluid">
    <div class="row mb-20">
        <div class="col-lg-4 mb-20">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Menus</h6>
                            <h3 class="mb-0"><?php echo count($content['menu']); ?></h3>
                            <small class="text-muted">Manage site navigation</small>
                        </div>
                        <div class="badge badge-primary">Active</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-20">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Pages</h6>
                            <h3 class="mb-0"><?php echo count($content['pages']); ?></h3>
                            <small class="text-muted">Content overview</small>
                        </div>
                        <div class="badge badge-success">Published</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-20">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Users</h6>
                            <h3 class="mb-0"><?php echo count($users); ?></h3>
                            <small class="text-muted">System users</small>
                        </div>
                        <div class="badge badge-warning">Admin</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-20">
        <div class="col-lg-4 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Menus</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($content['menu'])): ?>
                        <p class="text-muted">No menu items</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Type</th>
                                        <th>Order</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($content['menu'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['label'] ?? $item['id']); ?></td>
                                            <td><?php echo htmlspecialchars($item['type'] ?? 'page'); ?></td>
                                            <td><?php echo htmlspecialchars($item['order'] ?? '—'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditMenuModal('<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES); ?>')">
                                                    <i data-feather="edit-2"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Pages</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($content['pages'])): ?>
                        <p class="text-muted">No pages</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>ID</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($content['pages'] as $pageId => $pageData): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pageData['title'] ?? $pageId); ?></td>
                                            <td><?php echo htmlspecialchars($pageId); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openEditPageModal('<?php echo htmlspecialchars($pageId, ENT_QUOTES); ?>')">
                                                    <i data-feather="eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Users</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <p class="text-muted">No users</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $username => $hash): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($username); ?></td>
                                            <td>
                                                <?php if ($username === $_SESSION['admin_username']): ?>
                                                    <span class="badge badge-primary">Current</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openPasswordModal('<?php echo htmlspecialchars($username, ENT_QUOTES); ?>')">
                                                    <i data-feather="key"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-20">
        <div class="col-lg-6 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Add New User</h5>
                </div>
                <div class="card-body">
                    <form method="post" class="form-default" data-validate>
                        <input type="hidden" name="action" value="add_user">
                        <div class="form-group">
                            <label class="form-label required">Username</label>
                            <input type="text" name="username" class="form-control" data-required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Password</label>
                            <input type="password" name="password" class="form-control" data-required>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="plus"></i> Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-20">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Existing Users</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <p class="text-muted">No users</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $username => $hash): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($username); ?>
                                                <?php if ($username === $_SESSION['admin_username']): ?>
                                                    <span class="badge badge-primary" style="margin-left: 8px;">Current</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="d-flex gap-10">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openPasswordModal('<?php echo htmlspecialchars($username, ENT_QUOTES); ?>')">
                                                    <i data-feather="key"></i>
                                                </button>
                                                <?php if ($username !== $_SESSION['admin_username']): ?>
                                                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i data-feather="trash-2"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Menu Modal -->
<div class="modal fade" id="editMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_menu">
                    <input type="hidden" name="menu_id" id="editMenuId">
                    <div class="form-group">
                        <label class="form-label required">Label</label>
                        <input type="text" name="menu_label" id="editMenuLabel" class="form-control" data-required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Type</label>
                        <select name="menu_type" id="editMenuType" class="form-select" data-required>
                            <option value="page">Page</option>
                            <option value="link">External Link</option>
                        </select>
                    </div>
                    <div class="form-group" id="editMenuUrlGroup" style="display:none;">
                        <label class="form-label">URL</label>
                        <input type="url" name="menu_url" id="editMenuUrl" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Order</label>
                        <input type="number" name="menu_order" id="editMenuOrder" class="form-control" min="1" data-required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Page Modal -->
<div class="modal fade" id="editPageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editPageContent" style="max-height: 400px; overflow-y: auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="passwordForm">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="username" id="modalUsername">
                    <div class="form-group">
                        <label class="form-label">User</label>
                        <input type="text" id="modalUsernameDisplay" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">New Password</label>
                        <input type="password" name="password" id="modalPassword" class="form-control" data-required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editMenuModalInstance;
let editPageModalInstance;
let passwordModalInstance;

document.addEventListener('DOMContentLoaded', function () {
    const editMenuModal = document.getElementById('editMenuModal');
    const editPageModal = document.getElementById('editPageModal');
    const passwordModal = document.getElementById('passwordModal');

    if (editMenuModal) {
        editMenuModalInstance = new bootstrap.Modal(editMenuModal);
    }
    if (editPageModal) {
        editPageModalInstance = new bootstrap.Modal(editPageModal);
    }
    if (passwordModal) {
        passwordModalInstance = new bootstrap.Modal(passwordModal);
    }

    const editMenuType = document.getElementById('editMenuType');
    if (editMenuType) {
        editMenuType.addEventListener('change', toggleEditMenuUrl);
    }
});

function openPasswordModal(username) {
    document.getElementById('modalUsername').value = username;
    document.getElementById('modalUsernameDisplay').value = username;
    document.getElementById('modalPassword').value = '';
    if (passwordModalInstance) {
        passwordModalInstance.show();
    }
}

function openEditMenuModal(menuItemJson) {
    try {
        const item = JSON.parse(menuItemJson);
        document.getElementById('editMenuId').value = item.id || '';
        document.getElementById('editMenuLabel').value = item.label || '';
        document.getElementById('editMenuType').value = item.type || 'page';
        document.getElementById('editMenuOrder').value = item.order || '1';
        document.getElementById('editMenuUrl').value = item.url || '';
        toggleEditMenuUrl();
        if (editMenuModalInstance) {
            editMenuModalInstance.show();
        }
    } catch (e) {
        alert('Error loading menu item');
    }
}

function toggleEditMenuUrl() {
    const type = document.getElementById('editMenuType').value;
    const urlGroup = document.getElementById('editMenuUrlGroup');
    if (type === 'link') {
        urlGroup.style.display = 'block';
    } else {
        urlGroup.style.display = 'none';
    }
}

function openEditPageModal(pageId) {
    const pageData = <?php echo json_encode($content['pages'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const data = pageData[pageId] || {};

    let html = '<div style="color: #4680ff; text-transform: uppercase; margin-bottom: 16px;"><strong>' + pageId + '</strong></div>';
    for (const key in data) {
        const value = data[key];
        if (typeof value === 'object') {
            html += '<div style="margin-bottom: 12px;"><strong style="color: #4680ff;">' + key + '</strong><br>';
            html += '<div style="font-size: 12px; color: #6c757d; white-space: pre-wrap;">' + JSON.stringify(value, null, 2).substring(0, 200) + '...</div></div>';
        } else {
            html += '<div style="margin-bottom: 12px;"><strong style="color: #4680ff;">' + key + '</strong><br>';
            html += '<div style="font-size: 12px; color: #6c757d; white-space: pre-wrap;">' + String(value).substring(0, 100) + '</div></div>';
        }
    }

    document.getElementById('editPageContent').innerHTML = html;
    if (editPageModalInstance) {
        editPageModalInstance.show();
    }
}
</script>
