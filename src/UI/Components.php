<?php

namespace Morpheus\UI;

class Components
{
    private static array $theme = [
        'primary' => '#667eea',
        'secondary' => '#718096',
        'success' => '#48bb78',
        'danger' => '#f56565',
        'warning' => '#ed8936',
        'info' => '#4299e1',
        'light' => '#f7fafc',
        'dark' => '#2d3748'
    ];

    public static function setTheme(array $theme): void
    {
        self::$theme = array_merge(self::$theme, $theme);
    }

    public static function alert(string $message, string $type = 'info', bool $dismissible = true): string
    {
        $colors = [
            'success' => ['bg' => '#d4edda', 'border' => '#c3e6cb', 'text' => '#155724'],
            'danger' => ['bg' => '#f8d7da', 'border' => '#f5c6cb', 'text' => '#721c24'],
            'warning' => ['bg' => '#fff3cd', 'border' => '#ffeaa7', 'text' => '#856404'],
            'info' => ['bg' => '#d1ecf1', 'border' => '#bee5eb', 'text' => '#0c5460']
        ];

        $color = $colors[$type] ?? $colors['info'];
        $dismissBtn = $dismissible ? '<button type="button" style="float: right; background: none; border: none; font-size: 20px; cursor: pointer; color: ' . $color['text'] . ';" onclick="this.parentElement.remove()">&times;</button>' : '';

        return sprintf(
            '<div role="alert" style="padding: 12px 20px; margin-bottom: 16px; border: 1px solid %s; border-radius: 4px; background-color: %s; color: %s;">%s%s</div>',
            $color['border'],
            $color['bg'],
            $color['text'],
            $dismissBtn,
            htmlspecialchars($message)
        );
    }

    public static function badge(string $text, string $type = 'primary'): string
    {
        $color = self::$theme[$type] ?? self::$theme['primary'];
        
        return sprintf(
            '<span style="display: inline-block; padding: 4px 12px; font-size: 12px; font-weight: 600; line-height: 1; color: white; background-color: %s; border-radius: 12px; text-align: center;">%s</span>',
            $color,
            htmlspecialchars($text)
        );
    }

    public static function card(string $title, string $content, ?string $footer = null, array $options = []): string
    {
        $width = $options['width'] ?? '100%';
        $headerBg = $options['header_bg'] ?? '#f7fafc';
        
        $footerHtml = $footer ? sprintf('<div style="padding: 12px 20px; background-color: #f7fafc; border-top: 1px solid #e2e8f0;">%s</div>', $footer) : '';

        return sprintf(
            '<div style="width: %s; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 16px;">
                <div style="padding: 16px 20px; background-color: %s; border-bottom: 1px solid #e2e8f0; font-weight: 600; font-size: 18px;">%s</div>
                <div style="padding: 20px;">%s</div>
                %s
            </div>',
            $width,
            $headerBg,
            htmlspecialchars($title),
            $content,
            $footerHtml
        );
    }

    public static function modal(string $id, string $title, string $content, array $options = []): string
    {
        $width = $options['width'] ?? '600px';
        $showFooter = $options['show_footer'] ?? true;
        $closeBtn = $options['close_button'] ?? 'Close';
        $primaryBtn = $options['primary_button'] ?? null;

        $footerHtml = '';
        if ($showFooter) {
            $primaryBtnHtml = $primaryBtn ? sprintf('<button type="button" style="padding: 8px 16px; background-color: %s; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">%s</button>', self::$theme['primary'], htmlspecialchars($primaryBtn)) : '';
            $footerHtml = sprintf(
                '<div style="padding: 16px 20px; background-color: #f7fafc; border-top: 1px solid #e2e8f0; text-align: right;">
                    <button type="button" onclick="document.getElementById(\'%s\').style.display=\'none\'" style="padding: 8px 16px; background-color: #e2e8f0; color: #2d3748; border: none; border-radius: 4px; cursor: pointer;">%s</button>
                    %s
                </div>',
                $id,
                htmlspecialchars($closeBtn),
                $primaryBtnHtml
            );
        }

        return sprintf(
            '<div id="%s" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%%; height: 100%%; overflow: auto; background-color: rgba(0,0,0,0.5);">
                <div style="position: relative; background-color: white; margin: 5%% auto; width: %s; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); animation: slideDown 0.3s;">
                    <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 20px; font-weight: 600;">%s</h3>
                        <button type="button" onclick="document.getElementById(\'%s\').style.display=\'none\'" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #718096;">&times;</button>
                    </div>
                    <div style="padding: 20px;">%s</div>
                    %s
                </div>
            </div>
            <style>@keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }</style>',
            $id,
            $width,
            htmlspecialchars($title),
            $id,
            $content,
            $footerHtml
        );
    }

    public static function tabs(array $tabs, string $id = 'tabs'): string
    {
        $tabHeaders = '';
        $tabContents = '';
        $first = true;

        foreach ($tabs as $key => $tab) {
            $active = $first ? 'active' : '';
            $display = $first ? 'block' : 'none';
            $activeStyle = $first ? sprintf('border-bottom: 2px solid %s; color: %s;', self::$theme['primary'], self::$theme['primary']) : '';
            
            $tabHeaders .= sprintf(
                '<button type="button" class="tab-btn" onclick="openTab(\'%s\', \'%s\')" style="padding: 12px 24px; background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-size: 14px; font-weight: 500; color: #718096; transition: all 0.3s; %s">%s</button>',
                $id,
                $key,
                $activeStyle,
                htmlspecialchars($tab['title'])
            );

            $tabContents .= sprintf(
                '<div id="%s-%s" class="tab-content" style="display: %s; padding: 20px; animation: fadeIn 0.3s;">%s</div>',
                $id,
                $key,
                $display,
                $tab['content']
            );

            $first = false;
        }

        return sprintf(
            '<div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                <div style="display: flex; border-bottom: 1px solid #e2e8f0; background-color: #f7fafc;">%s</div>
                <div>%s</div>
            </div>
            <script>
            function openTab(group, tabId) {
                var contents = document.querySelectorAll("#" + group + " .tab-content");
                contents.forEach(function(content) { content.style.display = "none"; });
                
                var buttons = document.querySelectorAll("#" + group + " .tab-btn");
                buttons.forEach(function(btn) { 
                    btn.style.borderBottom = "2px solid transparent"; 
                    btn.style.color = "#718096";
                });
                
                document.getElementById(group + "-" + tabId).style.display = "block";
                event.target.style.borderBottom = "2px solid %s";
                event.target.style.color = "%s";
            }
            </script>
            <style>@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }</style>',
            $tabHeaders,
            $tabContents,
            self::$theme['primary'],
            self::$theme['primary']
        );
    }

    public static function accordion(array $items, string $id = 'accordion'): string
    {
        $html = '<div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">';
        
        foreach ($items as $index => $item) {
            $itemId = $id . '-' . $index;
            $html .= sprintf(
                '<div style="border-bottom: 1px solid #e2e8f0;">
                    <button type="button" onclick="toggleAccordion(\'%s\')" style="width: 100%%; padding: 16px 20px; background-color: #f7fafc; border: none; text-align: left; cursor: pointer; font-size: 16px; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                        <span>%s</span>
                        <span id="%s-icon" style="transition: transform 0.3s;">▼</span>
                    </button>
                    <div id="%s" style="display: none; padding: 20px; background-color: white;">%s</div>
                </div>',
                $itemId,
                htmlspecialchars($item['title']),
                $itemId,
                $itemId,
                $item['content']
            );
        }
        
        $html .= '</div>';
        $html .= '<script>
        function toggleAccordion(id) {
            var content = document.getElementById(id);
            var icon = document.getElementById(id + "-icon");
            if (content.style.display === "none" || content.style.display === "") {
                content.style.display = "block";
                icon.style.transform = "rotate(180deg)";
            } else {
                content.style.display = "none";
                icon.style.transform = "rotate(0deg)";
            }
        }
        </script>';
        
        return $html;
    }

    public static function button(string $text, string $type = 'primary', array $options = []): string
    {
        $color = self::$theme[$type] ?? self::$theme['primary'];
        $size = $options['size'] ?? 'medium';
        $href = $options['href'] ?? null;
        $onclick = $options['onclick'] ?? null;
        
        $padding = match($size) {
            'small' => '6px 12px',
            'large' => '12px 24px',
            default => '8px 16px'
        };

        $fontSize = match($size) {
            'small' => '13px',
            'large' => '16px',
            default => '14px'
        };

        $style = sprintf(
            'display: inline-block; padding: %s; font-size: %s; font-weight: 500; color: white; background-color: %s; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; text-align: center; transition: opacity 0.2s;',
            $padding,
            $fontSize,
            $color
        );

        $onclickAttr = $onclick ? sprintf(' onclick="%s"', htmlspecialchars($onclick)) : '';

        if ($href) {
            return sprintf('<a href="%s" style="%s"%s>%s</a>', htmlspecialchars($href), $style, $onclickAttr, htmlspecialchars($text));
        }

        return sprintf('<button type="button" style="%s"%s>%s</button>', $style, $onclickAttr, htmlspecialchars($text));
    }

    public static function buttonGroup(array $buttons): string
    {
        $html = '<div style="display: inline-flex; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
        
        foreach ($buttons as $index => $button) {
            $borderLeft = $index > 0 ? 'border-left: 1px solid rgba(255,255,255,0.3);' : '';
            $html .= sprintf(
                '<button type="button" onclick="%s" style="padding: 8px 16px; background-color: %s; color: white; border: none; cursor: pointer; font-size: 14px; %s">%s</button>',
                htmlspecialchars($button['onclick'] ?? ''),
                self::$theme[$button['type'] ?? 'primary'],
                $borderLeft,
                htmlspecialchars($button['text'])
            );
        }
        
        $html .= '</div>';
        return $html;
    }

    public static function breadcrumb(array $items): string
    {
        $html = '<nav aria-label="breadcrumb" style="padding: 12px 0;"><ol style="display: flex; list-style: none; padding: 0; margin: 0; flex-wrap: wrap;">';
        
        $count = count($items);
        foreach ($items as $index => $item) {
            $isLast = ($index === $count - 1);
            $separator = !$isLast ? '<span style="margin: 0 8px; color: #cbd5e0;">/</span>' : '';
            
            if (is_array($item)) {
                $html .= sprintf(
                    '<li><a href="%s" style="color: %s; text-decoration: none;">%s</a>%s</li>',
                    htmlspecialchars($item['href']),
                    self::$theme['primary'],
                    htmlspecialchars($item['text']),
                    $separator
                );
            } else {
                $color = $isLast ? '#2d3748' : self::$theme['primary'];
                $html .= sprintf('<li style="color: %s;">%s%s</li>', $color, htmlspecialchars($item), $separator);
            }
        }
        
        $html .= '</ol></nav>';
        return $html;
    }

    public static function pagination(int $currentPage, int $totalPages, string $baseUrl = '?page='): string
    {
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="pagination" style="display: flex; justify-content: center; margin: 20px 0;"><ul style="display: flex; list-style: none; padding: 0; margin: 0; gap: 4px;">';

        // Previous
        if ($currentPage > 1) {
            $html .= sprintf(
                '<li><a href="%s%d" style="display: block; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 4px; color: %s; text-decoration: none;">‹</a></li>',
                $baseUrl,
                $currentPage - 1,
                self::$theme['primary']
            );
        }

        // Pages
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $currentPage;
            $bgColor = $active ? self::$theme['primary'] : 'white';
            $textColor = $active ? 'white' : '#2d3748';
            
            $html .= sprintf(
                '<li><a href="%s%d" style="display: block; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 4px; background-color: %s; color: %s; text-decoration: none; font-weight: %s;">%d</a></li>',
                $baseUrl,
                $i,
                $bgColor,
                $textColor,
                $active ? '600' : '400',
                $i
            );
        }

        // Next
        if ($currentPage < $totalPages) {
            $html .= sprintf(
                '<li><a href="%s%d" style="display: block; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 4px; color: %s; text-decoration: none;">›</a></li>',
                $baseUrl,
                $currentPage + 1,
                self::$theme['primary']
            );
        }

        $html .= '</ul></nav>';
        return $html;
    }

    public static function statCard(string $title, string $value, ?string $trend = null, ?string $trendValue = null): string
    {
        $trendHtml = '';
        if ($trend && $trendValue) {
            $trendColor = $trend === 'up' ? self::$theme['success'] : self::$theme['danger'];
            $trendIcon = $trend === 'up' ? '↑' : '↓';
            $trendHtml = sprintf(
                '<div style="margin-top: 8px; font-size: 14px; color: %s; font-weight: 500;">%s %s</div>',
                $trendColor,
                $trendIcon,
                htmlspecialchars($trendValue)
            );
        }

        return sprintf(
            '<div style="padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; color: #718096; margin-bottom: 8px;">%s</div>
                <div style="font-size: 32px; font-weight: 700; color: #2d3748;">%s</div>
                %s
            </div>',
            htmlspecialchars($title),
            htmlspecialchars($value),
            $trendHtml
        );
    }

    public static function progressBar(int $percentage, ?string $label = null): string
    {
        $percentage = max(0, min(100, $percentage));
        $labelHtml = $label ? sprintf('<div style="margin-bottom: 8px; font-size: 14px; color: #2d3748;">%s</div>', htmlspecialchars($label)) : '';

        return sprintf(
            '<div>
                %s
                <div style="width: 100%%; height: 20px; background-color: #e2e8f0; border-radius: 10px; overflow: hidden;">
                    <div style="width: %d%%; height: 100%%; background-color: %s; transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">%d%%</div>
                </div>
            </div>',
            $labelHtml,
            $percentage,
            self::$theme['primary'],
            $percentage
        );
    }

    public static function table(array $headers, array $rows, array $options = []): string
    {
        $striped = $options['striped'] ?? true;
        $hover = $options['hover'] ?? true;

        $html = '<table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">';
        
        // Headers
        $html .= '<thead style="background-color: #f7fafc;"><tr>';
        foreach ($headers as $header) {
            $html .= sprintf('<th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #2d3748; border-bottom: 2px solid #e2e8f0;">%s</th>', htmlspecialchars($header));
        }
        $html .= '</tr></thead>';

        // Rows
        $html .= '<tbody>';
        foreach ($rows as $index => $row) {
            $bgColor = ($striped && $index % 2 === 1) ? '#f7fafc' : 'white';
            $hoverStyle = $hover ? 'onmouseover="this.style.backgroundColor=\'#edf2f7\'" onmouseout="this.style.backgroundColor=\'' . $bgColor . '\'"' : '';
            
            $html .= sprintf('<tr style="background-color: %s;" %s>', $bgColor, $hoverStyle);
            foreach ($row as $cell) {
                $html .= sprintf('<td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #2d3748;">%s</td>', $cell);
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    public static function dropdown(string $label, array $items, array $options = []): string
    {
        $id = $options['id'] ?? 'dropdown-' . uniqid();
        $type = $options['type'] ?? 'primary';
        $color = self::$theme[$type] ?? self::$theme['primary'];

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= sprintf(
                '<a href="%s" style="display: block; padding: 10px 16px; color: #2d3748; text-decoration: none; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor=\'#f7fafc\'" onmouseout="this.style.backgroundColor=\'white\'">%s</a>',
                htmlspecialchars($item['href'] ?? '#'),
                htmlspecialchars($item['text'])
            );
        }

        return sprintf(
            '<div style="position: relative; display: inline-block;">
                <button type="button" onclick="document.getElementById(\'%s\').style.display = document.getElementById(\'%s\').style.display === \'block\' ? \'none\' : \'block\'" style="padding: 8px 16px; background-color: %s; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                    %s ▼
                </button>
                <div id="%s" style="display: none; position: absolute; top: 100%%; left: 0; margin-top: 4px; min-width: 200px; background-color: white; border: 1px solid #e2e8f0; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000;">
                    %s
                </div>
            </div>
            <script>
            window.addEventListener("click", function(e) {
                if (!e.target.closest("#%s").parentElement) {
                    document.getElementById("%s").style.display = "none";
                }
            });
            </script>',
            $id,
            $id,
            $color,
            htmlspecialchars($label),
            $id,
            $itemsHtml,
            $id,
            $id
        );
    }

    public static function toast(string $message, string $type = 'info', int $duration = 3000): string
    {
        $id = 'toast-' . uniqid();
        $color = self::$theme[$type] ?? self::$theme['info'];

        return sprintf(
            '<div id="%s" style="position: fixed; bottom: 20px; right: 20px; padding: 16px 20px; background-color: %s; color: white; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 9999; animation: slideInRight 0.3s;">
                %s
            </div>
            <script>
            setTimeout(function() {
                var toast = document.getElementById("%s");
                toast.style.animation = "slideOutRight 0.3s";
                setTimeout(function() { toast.remove(); }, 300);
            }, %d);
            </script>
            <style>
            @keyframes slideInRight { from { transform: translateX(100%%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%%); opacity: 0; } }
            </style>',
            $id,
            $color,
            htmlspecialchars($message),
            $id,
            $duration
        );
    }
}
