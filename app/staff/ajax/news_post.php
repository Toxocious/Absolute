<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/news_post.php';

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Create']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $News_Title = null;
  if ( !empty($_GET['News_Title']) )
    $News_Title = Purify($_GET['News_Title']);

  $News_Content = null;
  if ( !empty($_GET['News_Content']) )
    $News_Content = Purify($_GET['News_Content']);

  if ( empty($News_Title) || empty($News_Content) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'Enter a valid news post title and news post content.',
    ]);

    exit;
  }

  switch ( $Action )
  {
    case 'Create':
      $Create_News_Post = CreateNewsPost($News_Title, $News_Content);

      echo json_encode([
        'Success' => $Create_News_Post['Success'],
        'Message' => $Create_News_Post['Message'],
      ]);
      break;
  }
