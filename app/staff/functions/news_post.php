<?php
  /**
   * Create a new news post.
   *
   * @param $News_Title
   * @param $News_Content
   */
  function CreateNewsPost
  (
    $News_Title,
    $News_Content
  )
  {
    global $PDO, $User_Data;

    try
    {
      $PDO->beginTransaction();

      $Create_News_Post = $PDO->prepare("
        INSERT INTO `news` (
          `News_Title`,
          `News_Text`,
          `News_Date`,
          `Poster_ID`
        ) VALUES ( ?, ?, ?, ? )
      ");
      $Create_News_Post->execute([
        $News_Title,
        $News_Content,
        date('m/d/y&\nb\sp;&\nb\sp;h:i A', time()),
        $User_Data['ID']
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'You have successfully created a new news post.',
    ];
  }
