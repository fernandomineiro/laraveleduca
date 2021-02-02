<?php
//não mexer na classe para insert e select no banco,
//facilita o processo de insert no PERFIL de ADMINISTRADOR do módulo
class Database extends PDO
{
    function __construct($DBTYPE, $DBHOST, $DBPORT, $DBNAME, $DBUSER, $DBPASS)
    {
        parent::__construct($DBTYPE . ':host=' . $DBHOST . ';port=' . $DBPORT . ';dbname=' . $DBNAME, $DBUSER, $DBPASS);
    }

    public function select($sql, $array = array(), $fetchMode = PDO::FETCH_ASSOC)
    {
        $sth = $this->prepare($sql);
        foreach ($array as $key => $value) {
            $sth->bindValue("$key", $value);
        }

        $sth->execute();
        return $sth->fetchAll($fetchMode);
    }

    public function insert($table, $data)
    {
        try {
            $aux = array_keys($data);

            $fieldNames = array();

            foreach ($aux as $key => $value)
                $fieldNames[] = "`" . $value . "`";

            $fieldNames = implode(',', $fieldNames);

            $fieldValues = ':' . implode(',:', array_keys($data));
            $sth = $this->prepare("INSERT INTO $table ($fieldNames) VALUES ($fieldValues)");

            foreach ($data as $key => $value) {
                $sth->bindValue(":$key", $value);
            }

            //$this->beginTransaction();
            $sth->execute();
            //$this->commit();
            return $this->lastInsertId();
        } catch (PDOExecption $e) {
            $this->rollback();
            print "Error!: " . $e->getMessage();
        }
    }

    public function update($table, $data, $where)
    {
        $fieldDetails = null;
        //$this->regLog($table, 'UPDATE', $data, $where);

        foreach ($data as $key => $value) {
            $fieldDetails .= "$key=:$key,";
        }

        $fieldDetails = rtrim($fieldDetails, ',');

        $sth = $this->prepare("UPDATE $table SET $fieldDetails WHERE $where");

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        $sth->execute();
    }

    public function delete($table, $where)
    {
        $this->exec("DELETE FROM {$table} WHERE {$where}");
    }

}