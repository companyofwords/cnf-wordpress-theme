<?php
/**
 * CNF Headless Theme - Fallback Template
 *
 * This theme is headless and does not render frontend pages.
 * All content is served via REST API to the React Router 7 frontend.
 *
 * If someone accesses the WordPress site directly, show this message.
 *
 * @package CNF_Headless
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Set 404 status
status_header(404);

// Get site information
$site_name = get_bloginfo('name');
$site_url = get_bloginfo('url');
$api_url = rest_url();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Headless WordPress | <?php echo esc_html($site_name); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            max-width: 600px;
            text-align: center;
        }

        h1 {
            color: #ee2742;
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 30px;
        }

        p {
            font-size: 18px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 20px;
        }

        .highlight {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 20px 0;
            color: #333;
            word-break: break-all;
        }

        .button {
            display: inline-block;
            background: #ee2742;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
            transition: background 0.3s ease;
        }

        .button:hover {
            background: #d11f38;
        }

        .button.secondary {
            background: #667eea;
        }

        .button.secondary:hover {
            background: #5568d3;
        }

        .info {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
            font-size: 14px;
            color: #999;
        }

        .info a {
            color: #667eea;
            text-decoration: none;
        }

        .info a:hover {
            text-decoration: underline;
        }

        .api-links {
            margin-top: 30px;
            text-align: left;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
        }

        .api-links h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .api-links ul {
            list-style: none;
        }

        .api-links li {
            margin: 8px 0;
            font-size: 13px;
        }

        .api-links a {
            color: #667eea;
            text-decoration: none;
            font-family: 'Courier New', monospace;
        }

        .api-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                padding: 40px 20px;
            }

            h1 {
                font-size: 24px;
            }

            p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚀</div>

        <h1>This is a Headless WordPress Installation</h1>

        <p>This WordPress site serves content via REST API to a decoupled React frontend application.</p>

        <p>If you're looking for the public website, please visit:</p>

        <a href="https://cnfminidumper.co.uk" class="button">Visit CNF Mini Dumpers</a>

        <p style="margin-top: 30px; font-size: 16px;">Are you an administrator?</p>

        <a href="<?php echo esc_url(admin_url()); ?>" class="button secondary">Access WordPress Admin</a>

        <div class="api-links">
            <h3>📡 Available REST API Endpoints:</h3>
            <ul>
                <li>
                    <strong>Bootstrap (All site data):</strong><br>
                    <a href="<?php echo esc_url(rest_url('app/v1/bootstrap')); ?>" target="_blank">
                        <?php echo esc_url(rest_url('app/v1/bootstrap')); ?>
                    </a>
                </li>
                <li>
                    <strong>CNF Machines:</strong><br>
                    <a href="<?php echo esc_url(rest_url('wp/v2/cnf_machine')); ?>" target="_blank">
                        <?php echo esc_url(rest_url('wp/v2/cnf_machine')); ?>
                    </a>
                </li>
                <li>
                    <strong>CNF Uses:</strong><br>
                    <a href="<?php echo esc_url(rest_url('wp/v2/cnf_use')); ?>" target="_blank">
                        <?php echo esc_url(rest_url('wp/v2/cnf_use')); ?>
                    </a>
                </li>
                <li>
                    <strong>FAQs:</strong><br>
                    <a href="<?php echo esc_url(rest_url('wp/v2/faq')); ?>" target="_blank">
                        <?php echo esc_url(rest_url('wp/v2/faq')); ?>
                    </a>
                </li>
                <li>
                    <strong>Pages:</strong><br>
                    <a href="<?php echo esc_url(rest_url('wp/v2/pages')); ?>" target="_blank">
                        <?php echo esc_url(rest_url('wp/v2/pages')); ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="info">
            <p>
                <strong>Theme:</strong> CNF Headless v<?php echo CNF_THEME_VERSION; ?><br>
                <strong>WordPress:</strong> <?php bloginfo('version'); ?><br>
                <strong>PHP:</strong> <?php echo phpversion(); ?><br>
                <strong>API Root:</strong> <a href="<?php echo esc_url(rest_url()); ?>" target="_blank"><?php echo esc_url(rest_url()); ?></a>
            </p>
            <p style="margin-top: 20px;">
                Built with <a href="https://wordsco.uk" target="_blank" rel="noopener">Wordsco</a>
            </p>
        </div>
    </div>
</body>
</html>
