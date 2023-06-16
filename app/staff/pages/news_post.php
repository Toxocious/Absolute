<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div style='padding: 5px;'>
        You aren't authorized to be here.
      </div>
    ";

    exit;
  }

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/news_post.php';
?>

<div style='display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>News Post</h3>
  </div>

  <div id='News_AJAX'></div>

  <table class='border-gradient' style='width: 600px;'>
    <tbody>
      <tr>
        <td style='padding: 5px;'>
          <input type='text' name='News_Post_Title' placeholder='News Post Title' style='width: 400px;' />
        </td>
      </tr>
    </tbody>

    <tbody>
      <tr>
        <td style='padding: 5px;'>
          <textarea cols='70' rows='10' name='News_Post_Content'></textarea>
        </td>
      </tr>
    </tbody>

    <tbody>
      <tr>
        <td style='padding: 5px;'>
          <button onclick='CreateNewsPost();'>
            Add New News Post
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
