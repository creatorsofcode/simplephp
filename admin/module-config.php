<?php
session_start();

// Check if user is logged in
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');

        :root {
            color-scheme: dark;
            --ink: #e2e8f0;
            --muted: #94a3b8;
            --panel: rgba(15, 23, 42, 0.72);
            --panel-strong: rgba(15, 23, 42, 0.9);
            --line: rgba(148, 163, 184, 0.25);
            --accent: #22d3ee;
            --accent-2: #a855f7;
            --glow: 0 22px 60px rgba(8, 12, 28, 0.45);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Space Grotesk', 'Segoe UI', sans-serif;
            background:
                radial-gradient(900px 600px at 10% 10%, rgba(34, 211, 238, 0.16), transparent 60%),
                radial-gradient(900px 600px at 90% 10%, rgba(168, 85, 247, 0.18), transparent 60%),
                #0b1020;
            min-height: 100vh;
            padding: 24px;
            color: var(--ink);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: var(--panel-strong);
            border-radius: 18px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: var(--glow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--line);
        }

        .header-content h1 {
            color: #f8fafc;
            font-size: 26px;
            margin-bottom: 6px;
        }

        .header-content p {
            color: var(--muted);
            font-size: 14px;
        }

        .module-badge {
            background: linear-gradient(135deg, rgba(34, 211, 238, 0.9), rgba(168, 85, 247, 0.9));
            color: #0b1020;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .back-btn {
            background: rgba(15, 23, 42, 0.8);
            color: #f8fafc;
            padding: 10px 22px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s ease, border-color 0.2s ease;
            display: inline-block;
            margin-bottom: 20px;
            border: 1px solid rgba(148, 163, 184, 0.35);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(34, 211, 238, 0.6);
        }

        .config-card {
            background: var(--panel);
            border-radius: 18px;
            padding: 30px;
            box-shadow: var(--glow);
            margin-bottom: 20px;
            border: 1px solid var(--line);
        }

        .config-card h2 {
            color: #f8fafc;
            font-size: 22px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .config-card h2::before {
            content: "⚙️";
            font-size: 22px;
        }

        .config-description {
            color: var(--muted);
            margin-bottom: 26px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .form-group .help-text {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-top: 6px;
            line-height: 1.5;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="url"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
            background: rgba(15, 23, 42, 0.8);
            color: #e2e8f0;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: rgba(34, 211, 238, 0.7);
            box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.18);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
            font-family: 'JetBrains Mono', monospace;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23e2e8f0' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.2s;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .checkbox-wrapper:hover {
            background: rgba(34, 211, 238, 0.08);
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #22d3ee;
        }

        .color-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .color-input-wrapper input[type="color"] {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            background: none;
        }

        .color-input-wrapper input[type="color"]::-webkit-color-swatch {
            border: 2px solid rgba(148, 163, 184, 0.4);
            border-radius: 10px;
        }

        .btn-save {
            background: linear-gradient(135deg, rgba(34, 211, 238, 0.9), rgba(168, 85, 247, 0.9));
            color: #0b1020;
            padding: 14px 30px;
            border: none;
            border-radius: 999px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 12px 24px rgba(34, 211, 238, 0.2);
        }

        .btn-save:hover {
            transform: translateY(-2px);
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .alert-success {
            background: rgba(34, 211, 238, 0.14);
            color: #e2e8f0;
            border-left: 3px solid rgba(34, 211, 238, 0.6);
        }

        .alert-error {
            background: rgba(248, 113, 113, 0.16);
            color: #fee2e2;
            border-left: 3px solid rgba(248, 113, 113, 0.6);
        }
    </style>
            flex: 1;
        }
        
        .color-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .color-input-wrapper input[type="color"] {
            width: 60px;
            height: 45px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .color-input-wrapper input[type="text"] {
            flex: 1;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #388e3c;
            border-left: 4px solid #4caf50;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }
        
        .field-group {
            border-left: 3px solid #667eea;
            padding-left: 20px;
            margin-bottom: 30px;
        }
        
        .required-indicator {
            color: #f44336;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="modules.php" class="back-btn">← Back to Modules</a>
        
        <div class="header">
            <div class="header-content">
                <h1><?= htmlspecialchars($moduleInfo['name']) ?></h1>
                <p>Configure module settings and options</p>
            </div>
            <div class="module-badge">v<?= htmlspecialchars($moduleInfo['version'] ?? '1.0.0') ?></div>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-<?= $success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="config-card">
            <h2>Configuration Settings</h2>
            <?php if (isset($configSchema['description'])): ?>
                <p class="config-description"><?= htmlspecialchars($configSchema['description']) ?></p>
            <?php endif; ?>
            
            <form method="POST">
                <?php foreach ($configSchema['fields'] ?? [] as $field): ?>
                    <div class="field-group">
                        <div class="form-group">
                            <label for="<?= htmlspecialchars($field['name']) ?>">
                                <?= htmlspecialchars($field['label']) ?>
                                <?php if (isset($field['required']) && $field['required']): ?>
                                    <span class="required-indicator">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php
                            $fieldType = $field['type'] ?? 'text';
                            $fieldName = $field['name'];
                            $fieldValue = $currentConfig[$fieldName] ?? ($field['default'] ?? '');
                            $placeholder = $field['placeholder'] ?? '';
                            ?>
                            
                            <?php if ($fieldType === 'text' || $fieldType === 'email' || $fieldType === 'url' || $fieldType === 'number'): ?>
                                <input 
                                    type="<?= htmlspecialchars($fieldType) ?>" 
                                    id="<?= htmlspecialchars($fieldName) ?>"
                                    name="<?= htmlspecialchars($fieldName) ?>"
                                    value="<?= htmlspecialchars($fieldValue) ?>"
                                    placeholder="<?= htmlspecialchars($placeholder) ?>"
                                    <?= (isset($field['required']) && $field['required']) ? 'required' : '' ?>
                                >
                                
                            <?php elseif ($fieldType === 'textarea'): ?>
                                <textarea 
                                    id="<?= htmlspecialchars($fieldName) ?>"
                                    name="<?= htmlspecialchars($fieldName) ?>"
                                    placeholder="<?= htmlspecialchars($placeholder) ?>"
                                    <?= (isset($field['required']) && $field['required']) ? 'required' : '' ?>
                                ><?= htmlspecialchars($fieldValue) ?></textarea>
                                
                            <?php elseif ($fieldType === 'select'): ?>
                                <select 
                                    id="<?= htmlspecialchars($fieldName) ?>"
                                    name="<?= htmlspecialchars($fieldName) ?>"
                                    <?= (isset($field['required']) && $field['required']) ? 'required' : '' ?>
                                >
                                    <?php foreach ($field['options'] ?? [] as $optValue => $optLabel): ?>
                                        <option 
                                            value="<?= htmlspecialchars($optValue) ?>"
                                            <?= $fieldValue == $optValue ? 'selected' : '' ?>
                                        >
                                            <?= htmlspecialchars($optLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                            <?php elseif ($fieldType === 'checkbox'): ?>
                                <div class="checkbox-wrapper">
                                    <input 
                                        type="checkbox" 
                                        id="<?= htmlspecialchars($fieldName) ?>"
                                        name="<?= htmlspecialchars($fieldName) ?>"
                                        value="1"
                                        <?= $fieldValue ? 'checked' : '' ?>
                                    >
                                    <label for="<?= htmlspecialchars($fieldName) ?>">
                                        <?= htmlspecialchars($field['checkbox_label'] ?? 'Enable this option') ?>
                                    </label>
                                </div>
                                
                            <?php elseif ($fieldType === 'color'): ?>
                                <div class="color-input-wrapper">
                                    <input 
                                        type="color" 
                                        id="<?= htmlspecialchars($fieldName) ?>_color"
                                        value="<?= htmlspecialchars($fieldValue ?: '#667eea') ?>"
                                        onchange="document.getElementById('<?= htmlspecialchars($fieldName) ?>').value = this.value"
                                    >
                                    <input 
                                        type="text" 
                                        id="<?= htmlspecialchars($fieldName) ?>"
                                        name="<?= htmlspecialchars($fieldName) ?>"
                                        value="<?= htmlspecialchars($fieldValue) ?>"
                                        placeholder="<?= htmlspecialchars($placeholder) ?>"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        oninput="document.getElementById('<?= htmlspecialchars($fieldName) ?>_color').value = this.value"
                                    >
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($field['help'])): ?>
                                <span class="help-text"><?= htmlspecialchars($field['help']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <button type="submit" name="save_config" class="btn-save">
                    💾 Save Configuration
                </button>
            </form>
        </div>
    </div>
</body>
</html>
