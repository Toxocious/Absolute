<?php
  class Dialogue
  {
    public $Dialogue;

    public function PrependText
    (
      string $Message,
      bool $Add_Linebreak = false
    )
    {
      if ( $Add_Linebreak )
        $this->Dialogue = "<div>{$Message}</div><br />" . $this->Dialogue;
      else
        $this->Dialogue = "<div>{$Message}</div>" . $this->Dialogue;

      return $this->Dialogue;
    }

    public function AppendText
    (
      string $Message,
      bool $Add_Linebreak = false
    )
    {
      $this->Dialogue = $this->Dialogue . "<div>{$Message}</div>";

      if ( $Add_Linebreak )
        $this->Dialogue .= '<br />';

      return $this->Dialogue;
    }
  }
