<?php

namespace Zyrtex\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Em desenvolvimento, se o banco ainda não existir, informamos claramente
                if ($e->getCode() === 1049) {
                    throw new Exception("Banco de dados '{$config['dbname']}' não encontrado no MySQL. Por favor, execute o script de migração schema.sql.");
                }
                throw new Exception("Falha de conexão com o banco de dados: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Executa uma consulta e retorna todos os resultados
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Executa uma consulta e retorna uma única linha
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Executa uma consulta e retorna uma única coluna do primeiro resultado
     */
    public static function fetchColumn(string $sql, array $params = []): mixed {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Executa uma consulta e retorna um valor escalar
     */
    public static function fetchValue(string $sql, array $params = []): mixed {
        return self::fetchColumn($sql, $params);
    }

    /**
     * Executa uma instrução (INSERT, UPDATE, DELETE) e retorna o total de linhas afetadas
     */
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Retorna o ID gerado pelo último INSERT
     */
    public static function lastInsertId(): string {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Inicia uma transação segura
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Confirma a transação atual
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    /**
     * Reverte a transação atual
     */
    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }
}
