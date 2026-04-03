<?php
// Admin Header Component
// Include this file at the top of admin pages after session start

function get_admin_header($page_title = 'Dashboard', $breadcrumbs = array()) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title><?php echo htmlspecialchars($page_title); ?> - Admin Dashboard</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="<?php echo isset($custom_css_path) ? htmlspecialchars($custom_css_path) : '../admin/css/admin-pro.css'; ?>">
        <style>
            /* Additional inline styles for common patterns */
            .admin-wrapper { min-height: 100vh; }
            .sidebar { background: linear-gradient(135deg, #1f2937 0%, #111827 100%); }
            .sidebar-logo img { max-width: 100%; height: auto; }
            .mt-0 { margin-top: 0 !important; }
            .hidden { display: none !important; }
        </style>
    </head>
    <body>
        <div class="admin-wrapper">
            <!-- Sidebar Navigation -->
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-logo">
                    <a href="admin.php">
                        <img src="../img/logo.png" alt="Esports Admin" title="Admin Dashboard">
                    </a>
                </div>

                <ul class="sidebar-menu">
                    <li class="sidebar-menu-item">
                        <a href="admin.php" class="sidebar-menu-link<?php echo ($page_title === 'Dashboard') ? ' active' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="admin.php" class="sidebar-menu-link<?php echo ($page_title === 'Users') ? ' active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="tournaments.php" class="sidebar-menu-link<?php echo ($page_title === 'Tournaments') ? ' active' : ''; ?>">
                            <i class="fas fa-trophy"></i>
                            <span>Tournaments</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="contents.php" class="sidebar-menu-link<?php echo ($page_title === 'News') ? ' active' : ''; ?>">
                            <i class="fas fa-newspaper"></i>
                            <span>News & Content</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="disputes_message.php" class="sidebar-menu-link<?php echo ($page_title === 'Disputes') ? ' active' : ''; ?>">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Disputes</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="result.php" class="sidebar-menu-link<?php echo ($page_title === 'Results') ? ' active' : ''; ?>">
                            <i class="fas fa-medal"></i>
                            <span>Results</span>
                        </a>
                    </li>

                    <div class="sidebar-divider" style="margin-top: auto;"></div>

                    <li class="sidebar-menu-item">
                        <a href="settings.php" class="sidebar-menu-link<?php echo ($page_title === 'Settings') ? ' active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="logout.php" class="sidebar-menu-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Header -->
                <header class="admin-header">
                    <div class="header-left">
                        <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
                        <?php if (!empty($breadcrumbs)): ?>
                            <nav class="breadcrumb">
                                <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                                    <?php if ($index > 0): ?>
                                        <span class="breadcrumb-separator">/</span>
                                    <?php endif; ?>
                                    <span class="breadcrumb-item<?php echo ($breadcrumb['active'] ?? false) ? ' active' : ''; ?>">
                                        <?php if (isset($breadcrumb['url'])): ?>
                                            <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>">
                                                <?php echo htmlspecialchars($breadcrumb['label']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($breadcrumb['label']); ?>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <div class="header-right">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="global-search" placeholder="Search..." class="global-search-input">
                        </div>

                        <div class="user-profile">
                            <div class="user-avatar" id="user-avatar">
                                <?php 
                                    $admin_initial = isset($_SESSION['admin_email']) ? strtoupper(substr($_SESSION['admin_email'], 0, 1)) : 'A';
                                    echo htmlspecialchars($admin_initial);
                                ?>
                            </div>
                            <div class="user-info">
                                <div class="name"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></div>
                                <div class="role">Administrator</div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content Will Go Here -->
                <div class="page-content">
    <?php
    return ob_get_clean();
}

// Function to close admin page
function close_admin_page() {
    ob_start();
    ?>
                </div>
            </div>
        </div>

        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
        <script>
            // Global search functionality
            document.getElementById('global-search').addEventListener('keyup', function(e) {
                var searchTerm = this.value.toLowerCase();
                if (searchTerm.length > 0) {
                    console.log('Searching for:', searchTerm);
                    // Add your search logic here
                }
            });

            // Alert auto-dismiss
            document.addEventListener('DOMContentLoaded', function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const closeBtn = alert.querySelector('.alert-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            alert.style.display = 'none';
                        });
                    }
                    // Auto dismiss after 5 seconds
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 5000);
                });

                // Table actions
                const deleteButtons = document.querySelectorAll('.btn-delete');
                deleteButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        if (!confirm('Are you sure you want to delete this item?')) {
                            e.preventDefault();
                        }
                    });
                });
            });

            // Hide preloader when page loads
            window.addEventListener('load', function() {
                const preloader = document.querySelector('.preloader');
                if (preloader) {
                    preloader.classList.add('hidden');
                }
            });
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// Function to show success alert
function show_success_alert($message) {
    return <<<HTML
    <div class="alert alert-success">
        <i class="fas fa-check-circle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Success</div>
            <div class="alert-message">$message</div>
        </div>
        <button type="button" class="alert-close"><i class="fas fa-times"></i></button>
    </div>
HTML;
}

// Function to show error alert
function show_error_alert($message) {
    return <<<HTML
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Error</div>
            <div class="alert-message">$message</div>
        </div>
        <button type="button" class="alert-close"><i class="fas fa-times"></i></button>
    </div>
HTML;
}

// Function to show warning alert
function show_warning_alert($message) {
    return <<<HTML
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Warning</div>
            <div class="alert-message">$message</div>
        </div>
        <button type="button" class="alert-close"><i class="fas fa-times"></i></button>
    </div>
HTML;
}
?>
