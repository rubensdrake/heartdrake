<?php 

namespace Drk\System;

class Mongo extends HeartDrake{

	private $con;

	public function __construct(){

		$config = $this->getConfig('db');

		$dsn = 'mongodb://'.$config['user'].':'.$config['password'].'@'.$config['server'].'/'.$config['dbname'];


		/*$this->conn = new \MongoClient("mongodb://admin:8uijkm90@localhost/mgc");*/
		$this->conn = new \MongoClient($dsn);
  		$this->conn = $this->conn->$config['dbname'];
	}

	/**
	 * Create autoincrement for a array of string represents ours collections
	 * @param  array  $data
	 * @return boolean
	 */
	public function createAutoIncrement($data = array()){
			$collection = 'autoIncDrk';
			
			$error = false;

			foreach ($data as $i => $newAutoInc) {
				$new = array('table'=>$newAutoInc,'nextId'=>1);
				if(!$this->createTableToInc($collection,$new))
					$error = true;
			}
			if(!$error)
			return true;	
	}


	/**
	 * Insert new table to inc 
	 * @param  String $collection
	 * @param  array  $table
	 * @return boolean
	 */
	public function createTableToInc($collection,$table = array()){
		$this->conn->$collection->remove(array('table'=>$table['table']));
		return $this->conn->$collection->insert($table);
	}

	/**
	 * Return nextId to table
	 * @param  String $table
	 * @return Integer
	 */
	public function getNextId($table){

		$where  = array('table'=>$table);

		$select = array('nextId'=>1,'_id'=>0);
		
		$id     = $this->conn->autoIncDrk->findOne($where,$select);
		
		/**
		 * Increment the nextId by one
		 * @var array
		 */
		$set  = array('$inc' => 
						array("nextId" => 1)
					);

		$this->conn->autoIncDrk->update(
									$where, 
									$set
								);
		return $id['nextId'];
	}



	/**
	 * Find function from mongo
	 * @param  array  $array
	 * @return array
	 */
	public function find($array = array()){
		return (array)iterator_to_array(
							$this->conn->$array['collection']->find(
																	$array['match'],
																	$array['select']
																)
						);
	}

	public function docExists($colecao,$criterio = array()){

		if($this->conn->$colecao->findOne($criterio))
			return true;
		else
			return false;
	}	

	public function count($colecao){
		return $this->conn->$colecao->count();
	}

	public function remove($collection,$query = array(),$t = true)
	{	

		if(!isset($collection)){
			
			throw new \Exception(Strings::get('collection_not_defined',get_called_class()));
		}

		if($this->docExists($collection,$query)){
			return $this->conn->$collection->remove($query);
		}else{
			return false;
		}
	}



}