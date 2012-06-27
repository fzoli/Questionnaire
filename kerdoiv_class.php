<?php
include 'class.php';

class Kerdoiv extends Dolgozo {
    
    private $lngStr;
    
    public function __construct() {
        parent::__construct();
        $this->init();
    }
    
    private function init() {
        if($this->userHandler->isSignedIn()) {
            $this->forward('admin.php');
        }
    }
    
    protected function initVars() {
        parent::initVars();
        $this->lngStr = 'Rendben/Zurecht';
    }
    
    private function getKapocs() {
        $query = 'SELECT * FROM kapocs';
        $ret = array();
        if ($result = $this->sql->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $ret[] = $row;
            }
        }
        return $ret;
    }
    
    private function getQuestions() {
        $questions = array();
        $szoveg = $this->getLangCol();
        $query = 'SELECT kerdes.azon, kerdes.kotelezo, kerdes.' . $szoveg . ' as szoveg, kerdes.extra_megnev as extra_opcio_id, megnev.' . $szoveg . ' as extra_opcio, kerdes.tipus
                  FROM kerdes
                  LEFT JOIN (megnev) ON (kerdes.extra_megnev = megnev.azon)';
        if ($result = $this->sql->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $kerdes = $row['azon'];
                unset($row['azon']);
                $row['opciok'] = array();
                $query = 'SELECT valasz.azon, megnev.' . $szoveg . ' as szoveg, megnev.azon as megnev_azon
                          FROM valasz
                          LEFT JOIN (megnev) ON (valasz.valasz = megnev.azon)
                          WHERE valasz.kerdes = ' . $kerdes;
                if ($result2 = $this->sql->query($query)) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $row['opciok'][$row2['azon']] = $row2['szoveg'];
                        $row['opciok_id'][$row2['azon']] = $row2['megnev_azon'];
                    }
                    $result2->free();
                }
                $questions[$kerdes] = $row;
            }
            $result->free();
        }
        return $questions;
    }
    
    private function removeQuestionPost() {
        unset($_SESSION['last_gen_post']);
        unset($_SESSION['lang']);
        unset($_POST);
    }
    
    private function reinitQuestionPost($questions) {
        //if (isset($_SESSION['last_gen_post']) && isset($_POST['submit']) && $_POST['submit'] == $this->getSysWord(2)) unset($_SESSION['last_gen_post']); //utólag betéve: ha tryInsert van, akkor ki kell pucolni az elõzõ beállításokat, mert lehetõség vált "radírozni" //mod2: nincs rá szükség, mert nincs automata radírozás
        foreach ($questions as $qKey => $dummy) {
            if (isset($_POST[$qKey])) {
                $_SESSION['last_gen_post'][$qKey] = $_POST[$qKey];
            }
            else {
                if (isset($_SESSION['last_gen_post'][$qKey])) $_POST[$qKey] = $_SESSION['last_gen_post'][$qKey];
            }
        }
    }
    
    public function doRequest() {
        if (isset($_POST['submit'])) {
            $word = $_POST['submit'];
            $insert = $this->getSysWord(2);
            $nullaz = $this->getSysWord(4);
            $nyelv = $this->lngStr;
            switch ($word) {
                case $insert:
                    $this->tryInsert();
                    break;
                case $nullaz:
                    $this->removeQuestionPost();
                    $this->gen(null, null, false, null);
                    break;
                case $nyelv:
                    if (($code = Captcha::check()) == 0) {
                        $this->saveLang();
                        $this->gen(null, null, null, null);
                    }
                    else {
                        $this->chooseLang($this->getCaptchaMsg($code, 'A megadott szöveg nem egyezik a képen láthatóval!<br/>Den Text ist anders als welcher im Bildschirm ist!', 'Lejárt munkamenet.<br />Ihre Sitzung ist abgelaufen.'));
                    }
                    break;
            }
        }
        else {
            if (isset($_POST['erase'])) $this->erase();
            $this->gen(null, null, null, null);
        }
    }
    
    private function erase() {
        foreach ($_POST['erase'] as $qKey => $dummy) {
            unset($_SESSION['last_gen_post'][$qKey]);
            unset($_POST[$qKey]);
        }
    }
    
    private function getFTD($c, $size, $qKey) {
        return $c == 1 ? '<td rowspan="'.$size.'"><input name="erase['.$qKey.']" type="image" src="imgs/erase.png" value="Erase" /></td>' : '';
    }
    
    private function getJSQuestionKorlat($questions, $kapcsok) {
        $arr = array();
        foreach ($questions as $key => $val) {
            $arr[$key] = array();
            $arr[$key]['kotelezo'] = $val['kotelezo'];
            $arr[$key]['opciok'] = $val['opciok_id'];
            $tmp = array();
            foreach ($kapcsok as $kapocs) {
                if ($kapocs['korlatolt_kerdes'] == $key) {
                    unset($kapocs['korlatolt_kerdes']);
                    $tmp[] = $kapocs;
                }
            }
            $arr[$key]['kapocs'] = $tmp;
        }
        $json = 'var questions = '.  json_encode($arr).';';
        $js = '<script type="text/javascript">'.PHP_EOL.
              '<!--//--><![CDATA[//><!--'.PHP_EOL.
              $json.PHP_EOL.
              '//--><!]]>'.PHP_EOL.
              '</script>'.PHP_EOL;
        return $js;
    }
    
    private function gen($questions, $err, $reinitPost, $kapcsok) {
        if (!$this->isLangSet()) {
            $this->chooseLang(null);
            return;
        }
        $this->setInserted(false);
        $kerdoiv = $this->getSysWord(1);
        if (!isset($questions)) $questions = $this->getQuestions();
        if (!isset($reinitPost)) $this->reinitQuestionPost($questions);
        else if ($reinitPost) $this->reinitQuestionPost($questions);
        if ($kapcsok == null) $kapcsok = $this->getKapocs();
        $jskorlat = $this->getJSQuestionKorlat($questions, $kapcsok);
        $str  = $jskorlat.'<div id="kerdoiv">'.$kerdoiv.'</div>';
        if (isset($err) && $err['code'] != 0) {
            $str .= '<div id="hibauzenet">'.$this->getError($err['code']).'<br />'.$this->getSysWord(3).': '.$err['question'].'</div>';
        }
        $str .= $this->formOpen;
        foreach ($questions as $qKey => $qValue) {
            if (isset($err)) {
                $cls = $err['question'] == $qKey ? 'hibas_kerdes' : 'kerdes';
            }
            else $cls = 'kerdes';
            $str .= '<span class="qu" id="qu'.$qKey.'"><div class="'.$cls.'">' . $qKey . ') ' . $qValue['szoveg'] . '</div>';
            $type = $qValue['tipus'];
            $opt = '';
            if ($type == 'one-input') {
                $postValue = htmlspecialchars($_POST[$qKey]);
                $opt = '<textarea name="'.$qKey.'" rows="5" cols="40">'.$postValue.'</textarea>';
            }
            else {
                switch ($type) {
                    case 'radio-button':
                        $type = 'radio';
                        break;
                    case 'checkbox':
                        $type = 'checkbox';
                        break;
                }
                $opt .= '<table style="position:relative;left:-10px">';
                $c = 0;
                $extra = $qValue['extra_opcio'];
                $size = count($qValue['opciok']) + (isset($extra) ? 1 : 0);
                foreach ($qValue['opciok'] as $aKey => $aValue) {
                    ++$c;
                    $optid = $qValue['opciok_id'][$aKey];
                    $ftd = $this->getFTD($c, $size, $qKey);
                    $opt .= '<tr>';
                    $opt .= $ftd;
                    $check = '';
                    if (isset($_POST[$qKey])) {
                        $check = in_array($aKey, $_POST[$qKey]) ? 'checked="checked"' : '';
                    }
                    $opt .= '<td><input class="answer an'.$optid.'" type="'.$type.'" name="'.$qKey.'[]" '.$check.' value="'.$aKey.'"> '.$aValue.'<br /></td>';
                    $opt .= '</tr>';
                }
                if (isset($extra)) {
                    ++$c;
                    $optid = $questions[$qKey]['extra_opcio_id'];
                    $ftd = $this->getFTD($c, $size, $qKey);
                    $check = '';
                    $val = '';
                    if (isset($_POST[$qKey])) {
                        foreach ($_POST[$qKey] as $pKey => $pValue) {
                            if ($pKey.'' == 'value') $val = htmlspecialchars($pValue);
                            else if ($pValue.'' == 'value') $check = 'checked="checked"';
                        }
                    }
                    $opt .= '<tr>'.$ftd.'<td><input class="answer an'.$optid.'" type="'.$type.'" name="'.$qKey.'[]" '.$check.' value="value"> '.$extra.': <input class="extra_input" type="input" name="'.$qKey.'[value]" value="'.$val.'"><br /></td></tr>';
                }
                $opt .= '</table>';
            }
            $str .= '<div class="valasz">' . $opt . '</div></span>';
        }
        $kuldes = $this->getSysWord(2);
        $nullaz = $this->getSysWord(4);
        $confirm = $this->getSysWord(6);
        $str .= '<div id="submit">';
        $str .= $this->getSubmitButton($kuldes, null, true);
        $str .= ' ';
        $str .= $this->getSubmitButton($nullaz, 'return confirm(\''.$confirm.'\')', true);
        $str .= '</div>';
        $str .= $this->formClose;
        echo $str;
    }
    
    //0: oké, a többi adatbázisban nyelvfüggõ hibakód
    //5: csak szóközbõl áll a szöveg
    private function validate($id,  $val) {
        switch ($id) {
            case '4':
            case '5':
            case '7':
            case '8':
            case '9':
            case '12':
            case '13': //perpill mindre egy kikötés van: nem állhat csak szóközbõl
                return ereg("^[ ]{0,}$", $val) ? 5 : 0;
            default: //ha adott kérdés nem validált
                return 0;
        }
    }
    
    private function getPostedKapocsList($questions, $kapcsok) {
        $list = array(); //hajjjajjj... kezdjünk neki.
        unset($_POST['submit']); //a submit érték eltüntetése, 1 ciklus spórolás
        foreach ($_POST as $questionId => $selecteds) { //minden megválaszolt kérdésen végigmegyünk
            $question = $questions[$questionId]; //kell minden adat a kérdésrõl
            if (is_array($selecteds)) { //ha tömb jött vissza, akkor vannak kijelölt lehetõségek
                unset($selecteds['value']); //ha tartalmaz extra értéket, kitöröljük, hogy fölösleges vizsgálat ne legyen
                foreach ($selecteds as $selected) { //az adott kérdéshez bejelölt értékeken végigmegyünk
                    if ($selected == null) continue; //ha az érték null, akkor ez nem feleletváasztós kérdés, tovább lépünk
                    if ($selected == 'value') { //ha az extra érték ki van választva, akkor...
                        $answerId = $question['extra_opcio_id'];
                        foreach ($kapcsok as $kapocs) { //végigmenve az összes kapcson ...
                            if ($kapocs['kerdes'] == $questionId && $kapocs['valasz'] == $answerId) //... megvizsgáljuk, hogy van-e hozzá kapocs és ha van, ...
                                $list[] = $kapocs['korlatolt_kerdes']; //hozzáadjuk a korlátozott kérdés azonosítóját a listához. hurrá!
                        }
                    }
                    // hogy ha nem extra érték, akkor lehetnek választható értékek is, tehát...
                    else foreach ($question['opciok_id'] as $answerId => $dummy) { //... az összes lehetõségen végigmegyünk, és ha ...
                        if ($selected == $answerId) { // ... egyezik a bejelölt érték az aktuális értékkel, akkor ...
                            foreach ($kapcsok as $kapocs) { //végigmenve az összes kapcson ...
                                if ($kapocs['kerdes'] == $questionId && $kapocs['valasz'] == $answerId) //... megvizsgáljuk, hogy van-e hozzá kapocs és ha van, ...
                                    $list[] = $kapocs['korlatolt_kerdes']; //hozzáadjuk a korlátozott kérdés azonosítóját a listához. hurrá!
                            }
                        }
                    }
                }
            }
        } //ezaz, minden le lett zárva. viszlát ciklusok. thx PHP, hogy gyors vagy.
        return $list;
    }
    
    //Hibakódok:
    //1: post szerkezet hiba
    //2: nem létezõ kérdés
    //3: nincs minden kitöltve
    //4: 1-nél több adat lett megadva
    //5: üres string
    //6: nem létezõ választás
    //x: adatbázisból a hibakód
    private function validatePost($questions, $kapcsok) {
        $code = 0;
        $qid = -1;
        $filled = true;
        $korlat = $this->getPostedKapocsList($questions, $kapcsok);
        foreach ($questions as $questionId => $question) {
            if(!$question['kotelezo'] && !in_array($questionId, $korlat)) continue; //ha a kérdés opcionális ÉS nincs korlátja, akkor next.
            $filled &= array_key_exists($questionId, $_POST);
            if (!$filled) {
                $qid = $questionId;
                break;
            }
        }
        if (!$filled) {
            //nincs minden kötelezõ kérdés kitöltve
            $code = 3;
        }
        else foreach ($_POST as $questionId => $answer) {
            if ($questionId == 'submit') continue;
            $kotelezo = $questions[$questionId]['kotelezo'];
            $korlatolt = false;
            if (!$kotelezo) $korlatolt = in_array($questionId, $korlat);
            if (array_key_exists($questionId, $questions)) {
                $type = $questions[$questionId]['tipus'];
                $opts = $questions[$questionId]['opciok'];
                if (is_array($answer)) {
                    if ($type == 'one-input') {
                        //nem lehet one-input, ha tömb rá a válasz
                        $code = 1;
                        $qid = $questionId;
                        break;
                    }
                    else {
                        $val = $answer['value'];
                        unset($answer['value']);
                        $length = count($answer);
                        if ($type == 'radio-button' && $length > 1) { //rádiógombos esetén több válasz jött
                            $code = 4;
                            $qid = $questionId;
                            break;
                        }
                        if ($length < 1 && ($kotelezo || $korlatolt)) { //minimum 1 válasznak kell lennie, ha kötelezõ
                            $code = 3;
                            $qid = $questionId;
                            break;
                        }
                        if (in_array('value', $answer)) { //ha kijelölték az "egyéb" opciót
                            $code = $this->validate($questionId, $val);
                            if ($code != 0) { //és a mezõ hibásan van kitöltve
                                $qid = $questionId;
                                break;
                            }
                        }
                        foreach ($answer as $value) {
                            if ($value == 'value') continue;
                            if(!array_key_exists($value, $opts)) { //nem létezõ válasz a kérdésre
                                $code = 6;
                                $qid = $questionId;
                                break; //nem kell a többit vizsgálni, ha van
                            }
                        }
                        if ($code != 0) break; //ha nem létezõ válasz, nem kell más feltételt vizsgálni
                    }
                }
                else {
                    if ($type != 'one-input') {
                        //ha nem tömb a válasz, nem lehet one-input
                        $code = 1;
                        $qid = $questionId;
                        break;
                    }
                    else {
                        if ($kotelezo || $korlatolt) {
                            $code = $this->validate($questionId, $answer);
                            if ($code != 0) { //a mezõ hibásan van kitöltve
                                $qid = $questionId;
                                break;
                            }
                        }
                    }
                }
            }
            else {
                //ilyen azonosítójú kérdés nem létezik
                $code = 2;
                $qid = $questionId;
                break;
            }
        }
        $ret = array();
        $ret['code'] = $code;
        $ret['question'] = $qid;
        return $ret;
    }
    
    private function setInserted($value) {
        $_SESSION['is_inserted'] = $value;
    }
    
    private function isInserted() {
        if (isset($_SESSION['is_inserted'])) return $_SESSION['is_inserted'];
        return false;
    }
    
    private function tryInsert() {
        if ($this->isInserted()) {
            $this->sayGoodBye();
            return;
        }
        $questions = $this->getQuestions();
        $kapcsok = $this->getKapocs();
        $this->reinitQuestionPost($questions);
        $err = $this->validatePost($questions, $kapcsok);
        if ($err['code'] != 0) $this->gen($questions, $err, false, $kapcsok);
        else $this->insert();
    }
    
    private function insertAdatlapInfo() {
        $lang = $this->sql->real_escape_string($this->getLangId());
        $query = "INSERT INTO adatlap_info (nyelv) VALUES ('".$lang."')";
        if($this->sql->query($query)) {
            return $this->sql->insert_id;
        }
        return -1;
    }
    
    private function getInsertQuery($id, $answerId) {
        return "INSERT INTO adatlap (id, valasz) VALUES ('".$id."', '".$answerId."')";
    }
    
    private function getInsertExtraQuery($id, $questionId, $answer) {
        return "INSERT INTO adatlap (id, kerdes, extra_valasz) VALUES ('".$id."', '".$questionId."', '".htmlspecialchars($answer)."')";
    }
    
    private function insertAdatlap($id) {
        foreach ($_POST as $questionId => $answer) {
            $result = true;
            if (is_array($answer)) {
                $val = $answer['value'];
                unset($answer['value']);
                $isVal = in_array('value', $answer);
                if ($isVal) {
                    $query = $this->getInsertExtraQuery($id, $questionId, $val);
                    $result = $this->sql->query($query);
                }
                foreach ($answer as $value) {
                    if ($value == 'value') continue;
                    $query = $this->getInsertQuery($id, $value);
                    $result = $this->sql->query($query);
                }
            }
            else if ($questionId != 'submit' && $this->validate($questionId, $answer) == 0) {
                $answer = $this->sql->real_escape_string($answer);
                $query = $this->getInsertExtraQuery($id, $questionId, $answer);
                $result = $this->sql->query($query);
            }
            if (!$result) die('Insert error:<br />'.$query.'<br>'.$this->sql->error);
        }
        $this->sayGoodBye();
        $this->setInserted(true);
        $this->removeQuestionPost();
    }
    
    private function chooseLang($err) {
        echo $this->formOpen;
        echo '<div id="nyelv_valaszto">'.
             'Kérem, válasszon egy nyelvet!'.
             '<br />'.
             'Bitte wählen Sie eine Sprache!'.
             '<br />';
        if (isset($err)) {
            $this->printError($err, true);
        }
        $this->showLangSelect();
        echo '<div id="captcha_keres">Kérem, adja meg a képen látható szöveget.<br/>Bitte geben Sie was für Text auf dem Bildschirm sehen.</div>';
        Captcha::show();
        echo '</div>'.
             '<div id="submit"><input name="submit" class="gomb" type="submit" value="'.$this->lngStr.'" /></div>';
        echo $this->formClose;
        echo '<div id="admin_url"><a href="admin.php">Admin</a></div>';
    }
    
    private function sayGoodBye() {
        echo '<div id="elkoszones">'.$this->getSysWord(5).'</div>';
    }
    
    private function insert() {
        $id = $this->insertAdatlapInfo();
        if ($id != -1) $this->insertAdatlap ($id);
    }
    
}

?>