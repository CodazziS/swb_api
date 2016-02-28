<?php

class Message extends ActiveRecord\Model {
	static $table_name = "Messages"; 
	static $sequence = "Messages_id_seq";
}
