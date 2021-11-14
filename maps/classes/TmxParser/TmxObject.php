<?php
  namespace TmxParser;

  abstract class TmxObject
  {
    /**
     * Get a parsed TmxObject by name.
     *
     * @param {string} $Name
     */
    public function __get
    (
      string $Name
    )
    {
      $Name = strtolower($Name);

      return $this->Name;
    }

    /**
     * Set a parsed TmxObject's value by name.
     *
     * @param {string} $Name
     * @param {mixed} $Value
     */
    public function __set
    (
      string $Name,
      mixed $Value
    )
    {
      $Name = strtolower($Name);

      $this->Name = $Value;
    }
  }
