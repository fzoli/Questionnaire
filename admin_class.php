<?php
include 'class.php';

class Admin extends Dolgozo {
    
    private $logStr;
    
    protected function initVars() {
        parent::initVars();
        $this->logStr = 'Login';
    }
    
    public function doRequest() {
        if (isset($_POST['submit'])) {
            switch ($_POST['submit']) {
                case $this->logStr:
                    $this->trySignIn();
                    break;
            }
        }
        else if (isset($_POST['subval'])) {
            switch ($_POST['subval']) {
                case 'signout':
                    $this->signOut();
                    break;
                case 'select':
                    $this->setSelectedForm();
                    break;
            }
            $this->show();
        }
        else {
            $this->show();
        }
    }
    
    private function signOut() {
        $this->setSelectedForm(null);
        $this->setLang(null);
        $this->userHandler->signOut();
    }
    
    private function show() {
        if ($this->userHandler->isSignedIn()) {
            $this->showForms();
        }
        else {
            $this->showLogin(null);
        }
    }
    
    private function trySignIn() {
        if (($code = Captcha::check()) != 0) {
            $this->showLogin($this->getCaptchaMsg($code, 'The text entered does not match the code shown!', 'Your session has timed out.'));
        }
        else if (!$this->userHandler->signIn($_POST['id'], $_POST['passwd'])) {
            $this->showLogin('Wrong ID or password!');
        }
        else {
            $this->saveLang();
            $this->showForms();
        }
    }
    
    private function showLogin($err) {
        echo $this->formOpen;
        echo '<div id="title">Admin</div>';
        $this->printError($err, isset($err));
        echo '<table><tr>'.
             '<td>ID: </td><td><input id="usr" name="id" value="'.$_POST['id'].'" type="input" /></td>'.
             '</tr><tr>'.
             '<td>Password: </td><td><input id="passwd" type="password" /><input id="sec_passwd" name="passwd" type="hidden" />'.
             '</td></tr></table>';
        echo '<span id="salt" style="visibility:hidden;position:fixed">'.$this->userHandler->genSalt().'</span>';
        echo '<div id="captcha_keres">Please enter the text from the image!</div>';
        Captcha::show();
        $this->showLangSelect();
        echo ' '.$this->getSubmitButton($this->logStr, null, false);
        echo '<div id="needjs">You have to enable JavaScript to sign in!</div>';
        echo '<div id="admin_url"><a href="index.php">Index</a></div>';
        echo $this->formClose;
    }
    
    private function getButtonText($val) {
        return '<img id="'.$val.'" class="icon" src="imgs/'.$val.'.png" />';
    }
    
    private function getFormCount($ls) {
        $count = 0;
        foreach($ls as $info) {
            if ($info['id'] == null) continue;
            $count++;
        }
        return $count;
    }
    
    private function getPagerText($ls, $count, $id) {
        if ($count == 0) return '<div id="message">'.$this->getSysWord(8).'</div>';
        $str = $this->getButtonText('begin').$this->getButtonText('prev').'<select id="select" name="select">';
        foreach($ls as $info) {
            $i = $info['id'];
            if ($i == null) continue;
            $sel = ($id == $i) ? 'selected="selected"' : '';
            $str .= '<option '.$sel.' value="'.$i.'">'.$i.'.</option>';
        }
        $str .= '</select>'.$this->getButtonText('next').$this->getButtonText('end');
        return $str;
    }
    
    private function isIdExists($ls, $id) {
        return $this->getFormLang($ls, $id) != null;
    }
    
    private function getFormLang($ls, $id) {
        if ($id == null) return null;
        foreach($ls as $langs) {
            if ($langs['id'] == $id) {
                return $langs['nyelv'];
            }
        }
        return null;
    }
    
    private function blobToChar($col) {
        return 'CAST('.$col.' AS CHAR(255) CHARACTER SET latin2)';
    }
    
    private function getFormAnswers($id) {
        $szoveg = $this->getLangCol();
        $kerdesSzoveg = $this->blobToChar('kerdes.'.$szoveg);
        $megnevSzoveg = $this->blobToChar('megnev.'.$szoveg);
        $extraMegnevSzoveg = '(SELECT megnev.'.$szoveg.' FROM megnev WHERE megnev.azon = kerdes.extra_megnev) as extra_megnev';
        $query = '(SELECT kerdes.azon as kerdes_id, '.
                          $kerdesSzoveg.' as kerdes, '.
                         'adatlap.valasz, '.
                          $extraMegnevSzoveg.', '.
                         'adatlap.extra_valasz
                  FROM adatlap
                  RIGHT JOIN (kerdes) ON (adatlap.kerdes = kerdes.azon)
                  WHERE id = '.$id.')
                  UNION
                  (SELECT kerdes.azon as kerdes_id, ' .
                          $kerdesSzoveg.' as kerdes, ' .
                          $megnevSzoveg.' as valasz, '.
                          $extraMegnevSzoveg.', '.
                         'adatlap.extra_valasz
                  FROM adatlap
                  RIGHT JOIN (valasz) ON (adatlap.valasz = valasz.azon)
                  RIGHT JOIN (kerdes) ON (valasz.kerdes = kerdes.azon)
                  RIGHT JOIN (megnev) ON (valasz.valasz = megnev.azon)
                  WHERE id = '.$id.')
                  ORDER BY kerdes_id ASC';
        $ret = array();
        if ($result = $this->sql->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $ret[] = $row;
            }
            
        }
        return $ret;
    }
    
    private function getFormText($ls, $count, $id) {
        if ($count == 0) return '';
        else {
            if (!$this->isIdExists($ls, $id)) {
                return '<div id="error">'.$this->getSysWord(9).'</div>';
            }
            else {
                $count = 0;
                $answers = $this->getFormAnswers($id);
                $answersCount = count($answers);
                $str = '<div id="frm_lang" style="background-image: url(\'flags/'.$this->getFormLang($ls, $id).'.jpg\')"></div>';
                $lastId = 0;
                foreach ($answers as $answer) {
                    ++$count;
                    $id = $answer['kerdes_id'];
                    if ($id != $lastId) {
                        if ($lastId != 0) $str .= '</dl>';
                        $str .= '<dl><dt>' . $id.') '.$answer['kerdes'].'</dt>';
                    }
                    $lastId = $id;
                    $valasz = $answer['valasz'];
                    $extraValasz = $answer['extra_valasz'];
                    $extraMegnev = $answer['extra_megnev'];
                    $extraMegnev = ($extraMegnev == null) ? '' : $extraMegnev.': ';
                    if ($valasz != null) $str .= '<dd>'.$valasz.'</dd>';
                    if ($extraValasz != null) $str .= '<dd>'.$extraMegnev.'<span style="font-style:italic">'.$extraValasz.'</span></dd>';
                    if ($answersCount == $count) $str .= '</dl>';
                }
                return $str;
            }
        }
    }
    
    private function getFormInfoList() {
        $query = 'SELECT id, nev as nyelv FROM adatlap_info RIGHT JOIN (nyelv) ON (nyelv = azon) ORDER BY id ASC';
        $ret = array();
        if ($result = $this->sql->query($query)) {
            while($row = $result->fetch_assoc()) {
                $ret[] = $row;
            }
        }
        return $ret;
    }
    
    private function setSelectedForm() {
        $id = $this->sql->real_escape_string($_POST['select']);
        $_SESSION['select'] = $id;
    }
    
    private function getSelectedForm() {
        if (isset($_SESSION['select'])) return $_SESSION['select'];
        return null;
    }
    
    private function getFlagCounterText($ls) {
        $count = array();
        foreach ($ls as $info) {
            $id = $info['id'];
            $nyelv = $info['nyelv'];
            if(!isset($count[$nyelv])) $count[$nyelv] = 0;
            if ($id != null) ++$count[$nyelv];
        }
        arsort($count);
        $str = '<table id="flags"><tr>';
        foreach ($count as $nyelv => $db) {
            $str .= '<td style="background-image: url(\'flags/'.$nyelv.'.jpg\')">'.$db.'</td>';
        }
        $str .= '</tr></table>';
        return $str;
    }
    
    private function getFirstId($ls) {
        $i = null;
        foreach ($ls as $v) {
            $i = $v['id'];
            if($i != null) break;
        }
        return $i;
    }
    
    private function showForms() {
        $ls = $this->getFormInfoList();
        $c = $this->getFormCount($ls);
        $id = $this->getSelectedForm();
        if ($id == null) $id = $this->getFirstId($ls);
        echo '<div id="printer">'.$this->getSysWord(1).': '.$id.' - '.$this->getFormLang($ls, $id).'</div>';
        echo '<div id="head"></div>';
        echo '<div id="bottom"></div>';
        echo $this->formOpen;
        echo '<table id="menu"><tr><td class="left">'.$this->getSysWord(7).', '.$this->userHandler->getUser().'!</td><td class="center">'.$this->getPagerText($ls, $c, $id).'</td><td class="right">'.$this->getButtonText('refresh').$this->getButtonText('signout').'</td></tr></table>';
        echo '<input id="subval" name="subval" type="hidden" />';
        echo $this->formClose;
        echo '<div id="content">'.$this->getFormText($ls, $c, $id).'</div>';
        echo $this->getFlagCounterText($ls);
    }
    
}

?>
