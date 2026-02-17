<?php

/**
 * Improved pagination function that generates table-based pagination controls
 * and returns data for the current page.
 *
 * @param string $base_query The base SQL query for fetching data
 * @param string $count_query The SQL query for counting total results
 * @param array $params Parameters for the SQL queries
 * @param int $current_page Current page number (1-based)
 * @param int $per_page Number of items per page
 * @param int $colspan Colspan value for table cells
 * @param string|null $onclick_template Template for onclick events (use [PAGE] placeholder)
 *
 * @return array|null Returns array with 'Data', 'Pagination', 'Total_Results', 'Total_Pages'
 */
function Pagination(
    string $base_query,
    string $count_query,
    array $params = [],
    int $current_page = 1,
    int $per_page = 10,
    int $colspan = 3,
    ?string $onclick_template = null
): ?array {
    global $PDO;

    try {
        $count_stmt = $PDO->prepare($count_query);
        $count_stmt->execute($params);
        $total_results = (int) $count_stmt->fetchColumn();

        if ($total_results === 0) {
            return [
                'Data' => [],
                'Pagination' => generateEmptyPaginationHtml($colspan),
                'Total_Results' => 0,
                'Total_Pages' => 0
            ];
        }

        $total_pages = ceil($total_results / $per_page);
        $current_page = max(1, min($current_page, $total_pages));
        $offset = ($current_page - 1) * $per_page;

        $data_query = $base_query . " LIMIT :limit OFFSET :offset";
        $data_stmt = $PDO->prepare($data_query);

        $all_params = $params;
        $all_params[':limit'] = $per_page;
        $all_params[':offset'] = $offset;

        $data_stmt->execute($all_params);

        $data = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

        $Pagination = generatePaginationHtml($current_page, $total_pages, $colspan, $onclick_template);

        return [
            'Data' => $data,
            'Pagination' => $Pagination,
            'Total_Results' => $total_results,
            'Total_Pages' => $total_pages,
            'Current_Page' => $current_page,
            'Per_Page' => $per_page
        ];
    } catch (PDOException $e) {
        HandleError($e);
        return null;
    }
}

/**
 * Generate the HTML for pagination controls in table format
 */
function generatePaginationHtml(int $current_page, int $total_pages, int $colspan, ?string $onclick_template): string
{
    $html = '<tr>';

    if ($current_page > 1) {
        $onclick = generateOnclick($onclick_template, 1);
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; cursor: pointer;'>
                <a href='javascript:void();' {$onclick}>&lt;&lt;</a>
            </td>
        ";
    } else {
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; color: #ccc;'>
                &lt;&lt;
            </td>
        ";
    }

    if ($current_page > 1) {
        $onclick = generateOnclick($onclick_template, $current_page - 1);
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; cursor: pointer;'>
                <a href='javascript:void();' {$onclick}>&lt;</a>
            </td>
        ";
    } else {
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; color: #ccc;'>
                &lt;
            </td>
        ";
    }

    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        $onclick = generateOnclick($onclick_template, $prev_page);
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; cursor: pointer;'>
                <a href='javascript:void();' {$onclick}>{$prev_page}</a>
            </td>
        ";
    } else {
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center;'>
                &nbsp;
            </td>
        ";
    }

    $html .= "
        <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; font-weight: bold;'>
            {$current_page}
        </td>
    ";

    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        $onclick = generateOnclick($onclick_template, $next_page);
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; cursor: pointer;'>
                <a href='javascript:void();' {$onclick}>{$next_page}</a>
            </td>
        ";
    } else {
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center;'>
                &nbsp;
            </td>
        ";
    }

    if ($current_page < $total_pages) {
        $onclick = generateOnclick($onclick_template, $current_page + 1);
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; cursor: pointer;'>
                <a href='javascript:void();' {$onclick}>&gt;</a>
            </td>
        ";
    } else {
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; color: #ccc;'>
                &gt;
            </td>
        ";
    }

    if ($current_page < $total_pages) {
        $onclick = generateOnclick($onclick_template, $total_pages);
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; cursor: pointer;'>
                <a href='javascript:void();' {$onclick}>&gt;&gt;</a>
            </td>
        ";
    } else {
        $html .= "
            <td colspan='{$colspan}' style='width: calc(100% / 7); text-align: center; color: #ccc;'>
                &gt;&gt;
            </td>
        ";
    }

    $html .= '</tr>';

    return $html;
}

/**
 * Generate onclick attribute or default Update_Box call
 */
function generateOnclick(?string $onclick_template, int $page): string
{
    if ($onclick_template) {
        $onclick_content = str_replace('[PAGE]', $page, $onclick_template);
        return "onclick=\"{$onclick_content}\"";
    } else {
        global $User_ID;
        $user_id = $User_ID ?? 0;
        return "onclick=\"Update_Box({$page}, {$user_id});\"";
    }
}

/**
 * Generate empty pagination HTML when no results
 */
function generateEmptyPaginationHtml(int $colspan): string
{
    return "
        <tr>
            <td colspan='" . ($colspan * 7) . "' style='text-align: center; padding: 20px;'>
                No results found.
            </td>
        </tr>
    ";
}
