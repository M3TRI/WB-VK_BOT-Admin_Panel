<?php
namespace Admin;

class DatabaseConfig
{
    private string $host;
    private string $user;
    private string $password;
    private string $dbname;
    private string $charset;

    public function __construct(
        string $host = "localhost",
        string $user = "sudo",
        string $password = '',
        string $dbname = "monahat",
        string $charset = "utf8"
    ) {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->charset = $charset;
    }

    public function getDsn(): string
    {
        return "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}