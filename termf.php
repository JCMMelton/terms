<?php

// A simple App to store and retrieve term, definition pairs

echo json_encode(process(getTag($_SERVER['QUERY_STRING'])));
closeDB();
// func zone
$link = null;
function closeDB() {
    if($link) {
        mysqli_close($link);
    }
}
function connect() {
    return mysqli_connect("localhost", "jcmm_login", "FriedWaffleBurrit0!23", "jcmm_wp");
}

function getTag($uri = null) {
    if($uri) {
        $rt = array();
        preg_match('/(\w*)=(\w*)&?/', $uri, $rt);
        return $rt[1];
    }
}

// converts the global $_POST array to a local array
function post_data() {
    $data = array();
    if(!empty($_POST)) {
        foreach($_POST as $key => $val) {
            $data[$key] = $val;
        }
    }
    return $data;
}

// converts the global $_POST array to a local array
// only returns keys that are passed in the keys array
function post_keys($keys = array()) {
    return key_filter(post_data(), $keys);
}
function is_assoc($array) {
    return (bool)count(array_filter(array_keys($array), 'is_string'));
}

// returns a subset of a data array with only the supplied keys
function key_filter($data = array(), $keys = array()) {
    $filter_data = array();
    if(is_assoc($keys)) {
        foreach($keys as $old => $new) {
            if(array_key_exists($old, $data)) {
                $filter_data[$new] = $data[$old];
            }
        }
    }
    else {  
        foreach($keys as $key) {
            if(array_key_exists($key, $data)) {
                $filter_data[$key] = $data[$key];
            }
        }
    }
    return $filter_data;
}

function sub_array_key_filter($data = array(), $keys = array()) {
    $filter_data = array();
    foreach($data as $d) {
        $filter_data[] = key_filter($d, $keys);
    }
    return $filter_data;
}

// takes in data, runs a function on based on the tag, returns result
function process($tag) {
    if(!in_array($tag, ["add", "search"])) return null;
    $datas = array(
        "add"    => array("term", "body", "username"),
        "search" => array("term"),
    );
    function add($data, $db) {
        return mysqli_query($db, "INSERT INTO terms (term, definition) VALUES ('".$data['term']."', '".$data['body']."')");
    };
    function search($data, $db) {
        return sub_array_key_filter(
            mysqli_query($db, "SELECT * FROM terms WHERE UPPER(terms.term) LIKE '%". strtoupper($data['term']) . "%'"), 
            array('id','term', 'definition', 'username', 'added_dtm')
        );
    };
    return $tag(post_keys($datas[$tag]), connect());
}