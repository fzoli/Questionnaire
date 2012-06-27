<?php

class AtwSql extends MySQLi {
	
	public function __construct($user, $passwd) {
		ini_set('magic_quotes_gpc', '0');
                $db = 'kerdoiv'; //TODO: localhost-ra kikommentezni
                //$db = $user; //TODO: atw-re kikommentezni, localhoston bekommentezni
		$this->connect('localhost', $user, $passwd, $db, '3306');
		if ($this->connect_error) die('Sql connection error ('.$this->connect_errno.'): '.$this->connect_error);
		$this->set_charset("latin2");
	}

	public function __destruct() {
		$this->close();
	}

}

class ConfAtwSql extends AtwSql {
    
    public function __construct($filePath) {
        include_once $filePath;
        parent::__construct(USR, PASSWD);
    }
    
}

class Captcha {
    
    private static $old;
    
    public static function show() {
        echo '<div id="captcha_div"><img id="captcha_img" src="captcha.php"/><br /><input id="captcha_input" maxlength="5" name="captcha" type="input"/></div>';
    }
    
    public static function check() {
        if (!isset(self::$old) || self::$old == null) return -1;
        $ok = strtolower($_POST['captcha']) == strtolower(self::$old);
        self::$old = null;
        return $ok ? 0 : 1;
    }

    public static function create() {
        self::$old = $_SESSION['captcha'];
        $_SESSION['captcha'] = self::genCaptcha();
    }
    
    private static function genCaptcha() {
        return substr(self::genReqID(),0,5);
    }

    private static function genReqID() {
        return Generator::genSalt();
    }
    
}

class Generator {
    
    public static function genSalt() {
        return md5(time()+microtime()+mt_rand()+mt_rand());
    }
    
}

class UserHandler {
    
    private $sql;
    
    public function __construct($sql) {
        $this->sql = $sql;
    }
    
    public function getSalt() {
        return $_SESSION['salt'];
    }
    
    public function genSalt() {
        $salt = Generator::genSalt();
        $_SESSION['salt'] = $salt;
        return $salt;
    }
    
    private function validateSignInData($id, $pass) {
        $data = $this->getUsrData($id);
        if (isset($data)) { //létezik a felhasználó
            $passwd = $data['passwd'];
            $passwd .= $this->getSalt();
            $passwd = sha1($passwd);
            if ($passwd == $pass) //egyezik a jelszó
                return true;
        }
        return false;
    }
    
    private function getUsrData($usr) {
        $query = "SELECT * FROM usr WHERE id ='".$this->sql->real_escape_string($usr)."'";
        if ($result = $this->sql->query($query)) {
            return $result->fetch_assoc();
        }
    }
    
    public function isSignedIn() {
        return isset($_SESSION['user']);
    }
    
    public function getUser() {
        if ($this->isSignedIn()) return $_SESSION['user'];
    }
    
    public function signOut() {
        unset($_SESSION['user']);
    }
    
    public function signIn($id, $passwd) {
        if ($this->validateSignInData($id, $passwd)) {
            $_SESSION['user'] = $id;
            return true;
        }
        return false;
    }
    
}

abstract class Dolgozo {
    
    private $langs;
    protected $sql, $userHandler, $formOpen, $formClose;
    
    public function __construct() {
        $this->sql = new ConfAtwSql('sql.php');
        session_start();
        Captcha::create();
        $this->initVars();
    }
    
    protected function getCaptchaMsg($code, $bad, $notInit) {
        switch ($code) {
            case -1:
                return $notInit;
            case 1:
                return $bad;
            default:
                return '';
        }
    }
    
    protected function forward($url) {
        header('Location: ' . $url);
        exit();
    }
    
    protected function getSubmitButton($val, $click, $enabled) {
        $disabled = $enabled ? '' : 'disabled="disabled"';
        $onclick = isset($click) ? 'onclick="'.$click.'"' : '';
        return '<input name="submit" class="gomb" type="submit" '.$onclick.' '.$disabled.' value="'.$val.'"/>';
    }
    
    protected function getLangId() {
        return array_search($this->getLang(), $this->langs);
    }
    
    protected function initVars() {
        $this->initLangs();
        $this->userHandler = new UserHandler($this->sql);
        $this->formOpen = '<form id="form" class="form" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
        $this->formClose = '</form>';
    }
    
    private function initLangs() {
        $query = 'SELECT * FROM nyelv';
        if($result = $this->sql->query($query)) {
            $this->langs = array();
            while ($row = $result->fetch_assoc()) {
                $a = array();
                $a['str'] = $row['nev'];
                $a['col'] = $row['oszlop'];
                $this->langs[$row['azon']] = $a;
            }
        }
    }
    
    public abstract function doRequest();
    
    protected function printError($err, $visible) {
        $visible = $visible ? '' : 'style="visibility:hidden;position:fixed"';
        echo '<div '.$visible.' id="hibauzenet">'.$err.'</div>';
    }
    
    protected function saveLang() {
        $this->setLang($_POST['language']);
    }
    
    protected function setLang($id) {
        if ($id == null) unset($_SESSION['lang']);
        else if (array_key_exists($id, $this->langs)) {
            $_SESSION['lang'] = $this->langs[$id];
        }
    }
    
    protected function isLangSet() {
        return isset($_SESSION['lang']);
    }
    
    protected function getLang() {
        if (!$this->isLangSet()) return $this->langs['1'];
        return $_SESSION['lang'];
    }
    
    protected function getLangCol() {
        $lang = $this->getLang();
        return $lang['col'];
    }
    
    protected function getLangStr() {
        $lang = $this->getLang();
        return $lang['str'];
    }

    protected function getSysWord($azon) {
        return $this->getStr($azon, 'rendszeruzenet');
    }
    
    protected function getWord($azon) {
        return $this->getStr($azon, 'megnev');
    }
    
    protected function getError($azon) {
        return $this->getStr($azon, 'hibauzenet');
    }
    
    private function getStr($azon, $table) {
        $query = "SELECT ".$this->getLangCol()." as szoveg
                  FROM ".$table."
                  WHERE azon = '" . $this->sql->real_escape_string($azon) . "'";
        if ($result = $this->sql->query($query)) {
            while ($row = $result->fetch_assoc()) {
                return $row['szoveg'];
            }
        }
    }
    
    protected function showLangSelect() {
        $selected = '1';
        if (isset($_POST['language'])) {
            $selected = $_POST['language'];
        }
        echo '<select id="nyelv_select" name="language">';
        for ($i=1; $i <= count($this->langs); $i++) {
            $sel = ($i == $selected) ? 'selected="selected"' : '';
            echo '<option value="'.$i.'" '.$sel.'>'.$this->langs[$i]['str'].'</option>';
        }
        echo '</select>';
    }
    
}

?>
