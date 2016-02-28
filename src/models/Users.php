<?php

class User extends ActiveRecord\Model {
	static $table_name = "Users"; 
	static $sequence = "Users_id_seq";
}
