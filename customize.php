<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

// Handle reset
if (isset($_GET['reset'])) {
    for ($i = 1; $i <= 6; $i++) {
        unset($_SESSION["folder_icon_bg_$i"]);
        unset($_SESSION["folder_icon_bg_color_$i"]);
    }
    unset($_SESSION['dashboard_bg']);
    $_SESSION['success'] = "Customization reset to default!";
    redirect('customize.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save background
    if (!empty($_POST['dashboard_bg'])) {
        $_SESSION['dashboard_bg'] = $_POST['dashboard_bg'];
    }
    
    // Save folder icon backgrounds
    for ($i = 1; $i <= 6; $i++) {
        if (!empty($_POST["folder_icon_bg_$i"])) {
            $_SESSION["folder_icon_bg_$i"] = $_POST["folder_icon_bg_$i"];
        }
        if (!empty($_POST["folder_icon_bg_color_$i"])) {
            $_SESSION["folder_icon_bg_color_$i"] = $_POST["folder_icon_bg_color_$i"];
        }
    }
    
    $_SESSION['success'] = "Customization saved successfully!";
    redirect('dashboard.php');
}

// Default colors for folder icons
$defaultColors = [
    '#3498db', '#2ecc71', '#9b59b6', 
    '#f1c40f', '#e67e22', '#e74c3c'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Dashboard - StudyHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <h1>Customize Dashboard</h1>
            </div>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" id="customizationForm">
                <h3>Background Settings</h3>
                <div class="form-group">
                    <label>Dashboard Background Image URL</label>
                    <input type="url" name="dashboard_bg" id="dashboard_bg" 
                           value="<?php echo $_SESSION['dashboard_bg'] ?? ''; ?>" 
                           placeholder="https://example.com/background.jpg">
                </div>

                <h3>Folder Icon Backgrounds</h3>
                <p class="form-help">Customize the background of each folder icon. You can use images from any website or solid colors.</p>
                
                <div class="folder-icon-customization">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="folder-customization-group">
                            <h4>Folder <?php echo $i; ?> Icon</h4>
                            <div class="customization-controls">
                                <div class="control-group">
                                    <label>Background Image URL</label>
                                    <input type="url" name="folder_icon_bg_<?php echo $i; ?>" 
                                           id="folder_icon_bg_<?php echo $i; ?>"
                                           value="<?php echo $_SESSION["folder_icon_bg_$i"] ?? ''; ?>" 
                                           placeholder="https://example.com/pattern.jpg">
                                    
                                    <div class="url-actions">
                                        <button type="button" class="btn btn-sm btn-outline test-url" data-target="folder_icon_bg_<?php echo $i; ?>">
                                            <i class="fas fa-eye"></i> Test URL
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline clear-url" data-target="folder_icon_bg_<?php echo $i; ?>">
                                            <i class="fas fa-times"></i> Clear
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="control-group">
                                    <label>Background Color</label>
                                    <div class="color-input-group">
                                        <input type="color" name="folder_icon_bg_color_<?php echo $i; ?>" 
                                               id="folder_icon_bg_color_<?php echo $i; ?>"
                                               value="<?php echo $_SESSION["folder_icon_bg_color_$i"] ?? $defaultColors[$i-1]; ?>">
                                        <span class="color-value"><?php echo $_SESSION["folder_icon_bg_color_$i"] ?? $defaultColors[$i-1]; ?></span>
                                    </div>
                                    <small>Solid color background (used if no image or as fallback)</small>
                                </div>
                            </div>
                            
                            <div class="folder-preview">
                                <div class="preview-folder-icon folder-<?php echo $i; ?>">
                                    <i class="fas fa-folder folder-icon"
                                       style="background-image: url('<?php echo $_SESSION["folder_icon_bg_$i"] ?? 'none'; ?>');
                                              background-color: <?php echo $_SESSION["folder_icon_bg_color_$i"] ?? $defaultColors[$i-1]; ?>;">
                                    </i>
                                </div>
                                <span>Live Preview</span>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    <button type="button" id="resetCustomization" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Reset to Default
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview Section -->
        <div class="card">
            <h3>Live Preview</h3>
            <div class="dashboard-preview">
                <div class="preview-background" id="previewBackground"
                     style="background-image: url('<?php echo $_SESSION['dashboard_bg'] ?? 'none'; ?>')">
                    <div class="preview-stats">
                        <div class="preview-stat">5 Pending</div>
                        <div class="preview-stat">12 Completed</div>
                        <div class="preview-stat">2 Missing</div>
                        <div class="preview-stat">8 Classes</div>
                    </div>
                    <div class="preview-folders-grid">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <div class="preview-folder">
                                <div class="folder-icon-wrapper folder-<?php echo $i; ?>">
                                    <i class="fas fa-folder folder-icon"
                                       style="background-image: url('<?php echo $_SESSION["folder_icon_bg_$i"] ?? 'none'; ?>');
                                              background-color: <?php echo $_SESSION["folder_icon_bg_color_$i"] ?? $defaultColors[$i-1]; ?>;">
                                    </i>
                                </div>
                                <div class="folder-info">
                                    <h4>Subject <?php echo $i; ?></h4>
                                    <p>Sample notes</p>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- URL Test Modal -->
    <div id="urlTestModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Test Image URL</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="testImageContainer">
                    <p>Image will appear here if URL is valid...</p>
                </div>
                <div class="modal-actions">
                    <button id="useTestedUrl" class="btn btn-primary">Use This Image</button>
                    <button id="closeTestModal" class="btn btn-outline">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const form = document.getElementById('customizationForm');
        const resetBtn = document.getElementById('resetCustomization');
        const modal = document.getElementById('urlTestModal');
        const testImageContainer = document.getElementById('testImageContainer');
        const useTestedUrlBtn = document.getElementById('useTestedUrl');
        const closeTestModal = document.getElementById('closeTestModal');
        const modalClose = document.querySelector('.modal-close');

        let currentTestTarget = null;

        // Reset confirmation
        resetBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to reset all customization to default?')) {
                window.location.href = 'customize.php?reset=1';
            }
        });

        // URL testing functionality
        document.querySelectorAll('.test-url').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                const url = document.getElementById(target).value;
                
                if (!url) {
                    alert('Please enter a URL first');
                    return;
                }
                
                testImageUrl(url, target);
            });
        });

        // Clear URL buttons
        document.querySelectorAll('.clear-url').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                document.getElementById(target).value = '';
                updateFolderPreview(target.split('_').pop());
            });
        });

        // Update previews on input
        document.querySelectorAll('input[type="url"], input[type="color"]').forEach(input => {
            input.addEventListener('input', function() {
                if (this.name === 'dashboard_bg') {
                    updateDashboardPreview(this.value);
                } else if (this.name.startsWith('folder_icon_bg_')) {
                    const folderNum = this.name.split('_').pop();
                    updateFolderPreview(folderNum);
                }
            });
        });

        // Modal functionality
        modalClose.addEventListener('click', closeModal);
        closeTestModal.addEventListener('click', closeModal);
        useTestedUrlBtn.addEventListener('click', function() {
            if (currentTestTarget) {
                const testedUrl = testImageContainer.querySelector('img')?.src;
                if (testedUrl) {
                    document.getElementById(currentTestTarget).value = testedUrl;
                    updateFolderPreview(currentTestTarget.split('_').pop());
                }
            }
            closeModal();
        });

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        function updateFolderPreview(folderNum) {
            const bgImage = document.getElementById(`folder_icon_bg_${folderNum}`).value;
            const bgColor = document.getElementById(`folder_icon_bg_color_${folderNum}`).value;
            
            // Update all previews for this folder - targeting the .folder-icon class
            document.querySelectorAll(`.folder-${folderNum} .folder-icon`).forEach(icon => {
                icon.style.backgroundImage = bgImage ? `url('${bgImage}')` : 'none';
                icon.style.backgroundColor = bgColor;
            });
            
            // Update color value display
            const colorValue = document.querySelector(`#folder_icon_bg_color_${folderNum}`).nextElementSibling;
            if (colorValue) {
                colorValue.textContent = bgColor;
            }
        }

        function updateDashboardPreview(url) {
            const preview = document.getElementById('previewBackground');
            preview.style.backgroundImage = url ? `url('${url}')` : 'none';
        }

        function testImageUrl(url, target) {
            currentTestTarget = target;
            
            testImageContainer.innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Testing image URL...</p>
                </div>
            `;
            
            modal.style.display = 'block';
            
            const img = new Image();
            img.onload = function() {
                testImageContainer.innerHTML = `
                    <div class="test-success">
                        <i class="fas fa-check-circle"></i>
                        <p>URL is valid! Image loaded successfully.</p>
                        <div class="test-image-preview">
                            <img src="${url}" alt="Test Image" style="max-width: 100%; max-height: 300px;">
                            <p><small>Dimensions: ${img.width} × ${img.height}</small></p>
                        </div>
                    </div>
                `;
            };
            
            img.onerror = function() {
                testImageContainer.innerHTML = `
                    <div class="test-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load image. Please check:</p>
                        <ul>
                            <li>URL is correct and complete</li>
                            <li>Image is accessible (no hotlink protection)</li>
                            <li>URL starts with https:// or http://</li>
                            <li>Try a different image source</li>
                        </ul>
                    </div>
                `;
            };
            
            img.src = url;
        }

        function closeModal() {
            modal.style.display = 'none';
            currentTestTarget = null;
        }

        // Initialize color value displays
        document.querySelectorAll('input[type="color"]').forEach(input => {
            const colorValue = input.nextElementSibling;
            if (colorValue) {
                colorValue.textContent = input.value;
            }
        });

        // Initialize previews
        updateDashboardPreview(document.getElementById('dashboard_bg').value);
        for (let i = 1; i <= 6; i++) {
            updateFolderPreview(i);
        }
    </script>
</body>
</html>