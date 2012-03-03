<?php
 

/****** CONFIG ******/

define('_DB_NAME_', 'your_db_name');
define('_DB_LOGIN_', 'root');
define('_DB_MDP_', '');
define('_DB_SERVER_', 'localhost');

/****** CORE ******/

class Obj {

	public $v = array();
	protected $id;
	protected $table;
	protected $key;
		
	public function __construct($table, $id_object=NULL) {
	
		$nb_key = 0;
		$result_fields = Db::inst()->query('DESCRIBE '.$table);
		while ($row_field = mysql_fetch_assoc($result_fields)) {
			if($row_field['Key']=='PRI') {
				$this->key = $row_field['Field'];
				$nb_key++;
			}
		}
		
		if($nb_key > 1) return false;

		$this->table = $table;
		
		if($id_object) {
			$this->id = $id_object;
			$this->v = Db::inst()->getRow('*', $this->table,  $this->key.'='.$this->id);
		}

	}

	public function add() {
		$this->id = Db::inst()->insert($this->table, $this->v);
	}
	
	public function update() {
		Db::inst()->update($this->table, $this->v, $this->key.'='.$this->id);
	}
	
	public function delete() {
		Db::inst()->delete($this->table, $this->key.'='.$this->id);
	}
	
}



class Db {
	
	private $link;
	private static $mysql;

	private function __construct($bdd = _DB_NAME_, $identifiant = _DB_LOGIN_, $mdp =_DB_MDP_, $serveur = _DB_SERVER_) {
		$this->link = mysql_connect($serveur, $identifiant, $mdp) or die( mysql_error() );
		mysql_select_db($bdd, $this->link) or die( mysql_error() );
	}
	
	// Execute la requete
	public function query($q) {
		return mysql_query($q, $this->link);
	}
	
	// Formate le where
	private static function getQueryWhere($where) {
		$sql = '';
		if(is_array($where)) {
			foreach($where as $key => $param) {
				if($key==0) {
					$sql .= ' WHERE '.$param;
				} else {
					$sql .= ' AND '.$param;
				}
			}
		} else {
			return ' WHERE '.$where;
		}
		return $sql;
	}
	
	// Formate la requete de selection
	private static function getQuerySelect($select, $from, $where=NULL, $groupby=NULL, $orderby=NULL, $limit=NULL) {
		$sql = 'SELECT '.$select.' FROM '.$from;
		if($where) $sql .= self::getQueryWhere($where);
		if($groupby) $sql .= ' GROUP BY '.$groupby;
		if($orderby) $sql .= ' GROUP BY '.$orderby;
		if($limit) $sql .= ' LIMIT '.$limit;
		return $sql;
	}
	
	// Formate la requete de suppression
	private static function getQueryDelete($table, $where=NULL) {
		$sql = 'DELETE FROM '.$table;
		if($where) $sql .= self::getQueryWhere($where);
		return $sql;
	}
	
	// Formate la requete d'insertion
	private static function getQueryInsert($table, $values) {
		foreach($values as $key => $value) {
			$array_key[] = '`'.$key.'`';
			$array_value[] = '\''.mysql_real_escape_string($value).'\'';
		}
		return 'INSERT INTO '.$table.' ('.implode(',', $array_key).') VALUES ('.implode(',', $array_value).')';
	}
	
	// Formate la requete de mise a jour
	private static function getQueryUpdate($table, $values, $where) {
		$array_value = array();
		foreach($values as $key => $value) {
			$array_value[] = $key.'=\''.mysql_real_escape_string($value).'\'';
		}
		return 'UPDATE '.$table.' SET '.implode(', ', $array_value).' '.self::getQueryWhere($where) ;
	}
	 
	// Retourne un tableau de l’ensemble des résultats d’une sélection
	public function getArray($select, $from, $where=NULL, $groupby=NULL, $orderby=NULL, $limit=NULL) {
		$i = 0;
		$r = array();
		$res = self::query(self::getQuerySelect($select, $from, $where, $groupby, $orderby, $limit));
		while ($l = mysql_fetch_assoc($res)) {
			foreach ($l as $clef => $valeur) $r[$i][$clef] = $valeur;
			$i++;
		}
		return $r;
	}
	
	// Sélectionne et retourne la première ligne des résultats.
	public function getRow($select, $from, $where=NULL, $groupby=NULL, $orderby=NULL) {
		$r = self::getArray($select, $from, $where, $groupby, $orderby, '0,1');
		return $r ? $r[0] : false;
	}
	
	// Récupère l’unique colonne demandée de la première ligne d’une sélection.
	public function getValue($select, $from, $where=NULL, $groupby=NULL, $orderby=NULL) {
		$r = self::getArray($select, $from, $where, $groupby, $orderby, '0,1');
		return $r[0][$select];
	}
	
	// Sélectionne et retourne un tableau composé d'une unique colonne des résultats d’une sélection.
	public function getValueArray($select, $from, $where=NULL, $groupby=NULL, $orderby=NULL, $limit=NULL) {
		$r = self::getArray($select, $from, $where, $groupby, $orderby, $limit);
		$valueArray = array();
		foreach($r as $value) {
			$valueArray[] = $value[$select];
		}
		return $valueArray;
	}
	
	//  Compter un nombre de résultat
	public function count($from, $where=NULL, $groupby=NULL) {
		$r = self::getArray('COUNT(*) as count', $from, $where, $groupby);
		return $r[0]['count'];
	}
	
	//  Insérer du contenu
	public function insert($table, $values) {
		self::query(self::getQueryInsert($table, $values));
		return mysql_insert_id();
	}
	
	// Supprime des éléments
	public function delete($table, $where) {
		self::query(self::getQueryDelete($table, $where));
	}
	
	// Met à jour un enregistrement.
	public function update($table, $values, $where) {
		self::query(self::getQueryUpdate($table, $values, $where));
	}
	
	// Retourne la dernière erreur SQL
	public function error() {
		return mysql_error($this->lien);
	}
	
	// Retourne la connexion a la base
	public static function inst() {
     if(is_null(self::$mysql)) self::$mysql = new Db();  
     return self::$mysql;
   }


}
