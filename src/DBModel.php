<?php

namespace Tigress;

use Exception;

/**
 * Class DBModel (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 1.0.0
 * @lastmodified 2024-09-05
 * @package Tigress\Model
 */
class DBModel extends Model
{
    /**
     * Database connection
     * @var Database
     */
    private Database $db;

    /**
     * Get the version of the Model
     *
     * @return string
     */
    public static function version(): string
    {
        return '1.0.0';
    }

    /**
     * Model constructor.
     *
     * @param object|null $data
     */
    public function __construct(Database $db, string $table, object $data = null)
    {
        $this->db = $db;
        $this->createModel();
        parent::__construct($data);
    }

    private function createModel(): void
    {
        $sql = "DESCRIBE " . $this->table;
        $this->db->query($sql);

        $data = [];
        if ($this->db->getRows() > 0) {
            foreach ($this->db->fetchAll() as $row) {
                $rowField = $row['Field'];
                $rowType = $row['Type'];
                $rowNull = $row['Null'];
                $rowDefault = $row['Default'];

                $type = $this->getFieldType($rowType);

                if ($rowNull = 'YES') {
                    $value = null;
                } else {
                    if ($rowDefault !== null) {
                        $value = $rowDefault;
                    } else {
                        if ($type === 'int') {
                            $value = 0;
                        } elseif ($type === 'float') {
                            $value = 0.0;
                        } elseif ($type === 'datetime') {
                            $value = '0000-00-00 00:00:00';
                        } else {
                            $value = '';
                        }
                    }
                }

                $array = [
                    $rowField => [
                        'value' => $value,
                        'type' => $type
                    ]
                ];

                $data = array_merge($data, $array);
            }
        }
        print('<pre>');
        print_r($data);
        print('</pre>');
        exit;
        $this->initiateModel($data);
    }

    /**
     * Get the field type
     *
     * @param mixed $type
     * @return string
     */
    private function getFieldType(mixed $type): string
    {
        if (preg_match('/int|tinyint|smallint|mediumint|bigint/', $type)) {
            return 'int';
        } elseif (preg_match('/float|double|decimal/', $type)) {
            return 'float';
        } elseif (preg_match('/varchar|text|char|blob/', $type)) {
            return 'string';
        } elseif (preg_match('/date|time|datetime|timestamp/', $type)) {
            return 'datetime';
        } else {
            return 'string'; // Default to string if type is unrecognized
        }
    }
}