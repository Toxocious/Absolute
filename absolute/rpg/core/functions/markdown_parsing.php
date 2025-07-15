<?php
    /**
     * Markdown to HTML conversion functions
     */

    /**
     * Convert markdown to HTML
     *
     * @param string $markdown The markdown text to convert
     * @return string The HTML output
     */
    function convert_markdown_to_html($markdown) {
        // First, let's normalize line endings
        $markdown = str_replace("\r\n", "\n", $markdown);

        // Process code blocks first to prevent processing markdown inside them
        $markdown = parse_code_blocks($markdown);

        // Process the other elements in the correct order
        $markdown = parse_headers($markdown);
        $markdown = parse_bold_italic($markdown);
        $markdown = parse_strikethrough($markdown);

        // Process images before links to handle clickable images properly
        $markdown = parse_images($markdown);
        $markdown = parse_links($markdown);

        $markdown = parse_blockquotes($markdown);
        $markdown = parse_horizontal_rules($markdown);

        // Parse tables before any list or paragraph processing
        // Mark table blocks to protect them from paragraph processing
        $markdown = parse_tables($markdown);

        // Parse task lists before regular lists
        $markdown = parse_task_lists($markdown);
        $markdown = parse_lists($markdown);

        $markdown = parse_inline_code($markdown);

        // Handle paragraphs last, but skip table blocks
        $markdown = parse_paragraphs($markdown);

        return $markdown;
    }

    /**
     * Parse headers (# Header)
     */
    function parse_headers($text) {
        return preg_replace_callback('/^(#{1,6})\s+(.+?)$/m', function($matches) {
            $level = strlen($matches[1]);
            return "<h$level>" . trim($matches[2]) . "</h$level>";
        }, $text);
    }

    /**
     * Parse bold and italic text
     */
    function parse_bold_italic($text) {
        // Bold: **text** or __text__
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.*?)__/s', '<strong>$1</strong>', $text);

        // Italic: *text* or _text_
        $text = preg_replace('/\*([^*\n]+)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/_([^_\n]+)_/s', '<em>$1</em>', $text);

        return $text;
    }

    /**
     * Parse strikethrough
     */
    function parse_strikethrough($text) {
        return preg_replace('/~~(.*?)~~/s', '<del>$1</del>', $text);
    }

    /**
     * Parse links [text](url)
     */
    function parse_links($text) {
        return preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
    }

    /**
     * Parse images ![alt](url) and clickable images [![alt](url)](link)
     */
    function parse_images($text) {
        // First, handle clickable images pattern: [![alt](img)](url)
        $text = preg_replace_callback('/\[!\[([^\]]*)\]\(([^)]+)\)\]\(([^)]+)\)/', function($matches) {
            $alt = $matches[1];
            $img_url = $matches[2];
            $link_url = $matches[3];
            return "<a href=\"$link_url\"><img src=\"$img_url\" alt=\"$alt\"></a>";
        }, $text);

        // Then handle regular images: ![alt](url)
        $text = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1">', $text);

        return $text;
    }

    /**
     * Parse task lists
     */
    function parse_task_lists($text) {
        // Match task list items with either checked or unchecked boxes: [x] or [ ]
        return preg_replace_callback('/^([\s]*)[-\*\+]\s+\[([ xX])\]\s+(.+)$/m', function($matches) {
            $indentation = $matches[1];
            $checked = (strtolower($matches[2]) === 'x') ? ' checked' : '';
            $content = $matches[3];

            // Create HTML checkbox input with the appropriate checked state
            $checkbox = "<input type=\"checkbox\"$checked>";

            // Return a list item with the checkbox and the content
            return "$indentation<li class=\"task-list-item\">$checkbox $content</li>";
        }, $text);
    }

    /**
     * Parse lists (ordered and unordered)
     */
    function parse_lists($text) {
        // First, handle nested unordered lists
        $text = preg_replace_callback('/(?:(?:^|\n)(?:[\*\-\+] .+)(?:\n  [\*\-\+] .+)*)+/m', function($matches) {
            $list_content = $matches[0];

            // Replace nested items (with indentation)
            $list_content = preg_replace_callback('/^(  |\t)[\*\-\+] (.+)$/m', function($nested) {
                return "<li>$nested[2]</li>";
            }, $list_content);

            // Process main level items and wrap nested items in <ul>
            $list_content = preg_replace_callback('/^[\*\-\+] (.+)(?:\n<li>(.+)<\/li>)*$/m', function($item) {
                $main_item = $item[1];
                $nested_items = isset($item[2]) ? $item[0] : '';

                // If we have nested items
                if (strpos($nested_items, '<li>') !== false) {
                    // Extract just the nested <li> elements
                    preg_match_all('/<li>(.+?)<\/li>/', $nested_items, $nested_matches);
                    $nested_lis = '';
                    foreach ($nested_matches[0] as $li) {
                        $nested_lis .= $li . "\n";
                    }
                    return "<li>$main_item\n<ul>\n$nested_lis</ul>\n</li>";
                } else {
                    return "<li>$main_item</li>";
                }
            }, $list_content);

            return "<ul>\n$list_content\n</ul>";
        }, $text);

        // Regular unordered lists (fallback for simpler lists)
        $text = preg_replace_callback('/(?:(?:^|\n)[\*\-\+] [^\n]+)+/', function($matches) {
            $list = preg_replace('/^[\*\-\+] ([^\n]+)$/m', '<li>$1</li>', $matches[0]);
            return "<ul>\n$list\n</ul>";
        }, $text);

        // Then handle nested ordered lists
        $text = preg_replace_callback('/(?:^|\n)(?:(?:\d+\. .+)(?:\n.+)*)+/m', function($matches) {
            $list_content = $matches[0];
            $lines = explode("\n", $list_content);
            $result = '';
            $in_nested_list = false;
            $nested_content = '';

            foreach ($lines as $i => $line) {
                $line = rtrim($line);
                if (empty($line)) continue;

                // Main level item (no indentation)
                if (preg_match('/^(\d+)\. (.+)$/', $line, $matches)) {
                    // If we were in a nested list, close it and attach to previous item
                    if ($in_nested_list) {
                        // Close the nested list and attach it to the previous main item
                        $result = rtrim($result, "\n");
                        $result = preg_replace('/<\/li>$/', "<ol>\n$nested_content</ol>\n</li>\n", $result);
                        $in_nested_list = false;
                        $nested_content = '';
                    }

                    // Add the main level item
                    $result .= "<li>{$matches[2]}</li>\n";
                }
                // Nested item (has indentation)
                else if (preg_match('/^(\s+)(\d+)\. (.+)$/', $line, $matches)) {
                    $in_nested_list = true;
                    $nested_content .= "<li>{$matches[3]}</li>\n";

                    // If this is the last line, close the nested list
                    if ($i === count($lines) - 1) {
                        $result = rtrim($result, "\n");
                        $result = preg_replace('/<\/li>$/', "<ol>\n$nested_content</ol>\n</li>\n", $result);
                    }
                }
            }

            return "<ol>\n$result</ol>";
        }, $text);

        // Regular ordered lists (fallback for simpler lists)
        $text = preg_replace_callback('/(?:(?:^|\n)\d+\. [^\n]+)+/', function($matches) {
            $list = preg_replace('/^\d+\. ([^\n]+)$/m', '<li>$1</li>', $matches[0]);
            return "<ol>\n$list\n</ol>";
        }, $text);

        return $text;
    }

    /**
     * Parse code blocks
     */
    function parse_code_blocks($text) {
        return preg_replace_callback('/```(.*?)\n([\s\S]*?)```/s', function($matches) {
            $language = trim($matches[1]);
            $code = htmlspecialchars($matches[2]);
            $class = !empty($language) ? " class=\"language-$language\"" : "";
            return "<pre><code$class>$code</code></pre>";
        }, $text);
    }

    /**
     * Parse inline code
     */
    function parse_inline_code($text) {
        return preg_replace('/`([^`\n]+)`/', '<code>$1</code>', $text);
    }

    /**
     * Parse blockquotes
     */
    function parse_blockquotes($text) {
        return preg_replace_callback('/(?:^|\n)> ((?:.+(?:\n> ?.*)*))/m', function($matches) {
            // Get the blockquote content by removing the '> ' prefix from each line
            $content = preg_replace('/^> ?/m', '', $matches[1]);

            // Trim to remove any trailing whitespace or newlines that might cause empty paragraphs
            $content = trim($content);

            // If the content already has paragraph tags, don't add them
            if (!preg_match('/^<p>/', $content)) {
                // Replace single newlines with <br> tags inside the blockquote
                $content = preg_replace('/\n(?!\n)/', '<br>', $content);

                // Wrap content in paragraph tags
                $content = "<p>$content</p>";
            }

            return "<blockquote>\n$content\n</blockquote>";
        }, $text);
    }

    /**
     * Parse horizontal rules
     */
    function parse_horizontal_rules($text) {
        return preg_replace('/^(?:[\t ]*)(?:-{3,}|[=]{3,}|[*]{3,})(?:[\t ]*)$/m', '<hr>', $text);
    }

    /**
     * Parse paragraphs
     */
    function parse_paragraphs($text) {
        // First clean up any excessive newlines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Split by double newlines
        $blocks = preg_split('/\n\n/', $text);
        $result = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) continue;

            // Skip wrapping in <p> if it's already a block-level element
            // Check for table, list, blockquote, heading, etc.
            if (preg_match('/^<(?:table|thead|tbody|tr|th|td|ul|ol|li|blockquote|h[1-6]|pre|hr)/i', $block)) {
                $result[] = $block;
            } else {
                // Only replace newlines with <br> within paragraphs if there are no block-level HTML elements
                $block = preg_replace('/\n/', '<br>', $block);
                $result[] = "<p>$block</p>";
            }
        }

        return implode("\n\n", $result);
    }

    /**
     * Parse markdown tables
     */
    function parse_tables($text) {
        // Find tables with pattern: header row, delimiter row, and at least one data row
        return preg_replace_callback('/^\|.*\|[ \t]*\n\|[ :|-]+\|[ \t]*\n(\|.*\|[ \t]*\n)+/m', function($matches) {
            $table_text = $matches[0];
            $lines = explode("\n", trim($table_text));

            // Parse header row
            $header_line = array_shift($lines);
            $header_cells = explode('|', trim($header_line, '|'));

            // Parse delimiter row to determine alignments
            $delimiter_line = array_shift($lines);
            $delimiter_cells = explode('|', trim($delimiter_line, '|'));

            $alignments = [];
            foreach ($delimiter_cells as $delimiter) {
                $delimiter = trim($delimiter);
                if (preg_match('/^:-+:$/', $delimiter)) {
                    $alignments[] = 'center';
                } else if (preg_match('/^-+:$/', $delimiter)) {
                    $alignments[] = 'right';
                } else if (preg_match('/^:-+$/', $delimiter)) {
                    $alignments[] = 'left';
                } else {
                    $alignments[] = '';
                }
            }

            // Build header HTML
            $header_html = "<tr>\n";
            foreach ($header_cells as $i => $cell) {
                $alignment = isset($alignments[$i]) ? $alignments[$i] : '';
                $style = $alignment ? " style=\"text-align: $alignment\"" : '';
                $header_html .= "<th$style>" . trim($cell) . "</th>\n";
            }
            $header_html .= "</tr>";

            // Build body HTML
            $body_html = "";
            foreach ($lines as $line) {
                if (trim($line) === '') continue;

                $row_html = "<tr>\n";
                $cells = explode('|', trim($line, '|'));
                foreach ($cells as $i => $cell) {
                    $alignment = isset($alignments[$i]) ? $alignments[$i] : '';
                    $style = $alignment ? " style=\"text-align: $alignment\"" : '';
                    $row_html .= "<td$style>" . trim($cell) . "</td>\n";
                }
                $row_html .= "</tr>\n";
                $body_html .= $row_html;
            }

            // Assemble the complete table and mark it as a block-level element
            // This prevents paragraph processing from adding <br> tags
            return "<table class='border-gradient' style='width: 100%;'>\n<thead>\n$header_html\n</thead>\n<tbody>\n$body_html</tbody>\n</table>";
        }, $text);
    }
