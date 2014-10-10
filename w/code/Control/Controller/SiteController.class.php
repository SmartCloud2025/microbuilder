<?php
/**
 * 系统管理
 */
namespace Control\Controller;
use Core\Model\Site;
use Think\Controller;
use Think\Storage;

class SiteController extends Controller {
    
    public function commonAction() {
        Site::loadSettings();
        $settings = C('SITE');
        if(IS_POST) {
            $settings[Site::OPT_TITLE] = I('post.title');
            $settings[Site::OPT_STATCODE] = I('post.statcode', '', '');
            Site::saveSettings($settings);
            $this->success('操作成功');
            exit;
        }
        $entity = array();
        $entity['title'] = $settings[Site::OPT_TITLE];
        $entity['statcode'] = $settings[Site::OPT_STATCODE];
        $this->assign('entity', $entity);
        C('FRAME_CURRENT', U('control/site/common'));
        $this->display();
    }
    
    public function closeAction() {
        Site::loadSettings();
        $settings = C('SITE');
        if(IS_POST) {
            $close = coll_elements(array('close', 'tips'), I('post.'));
            $settings[Site::OPT_CLOSE] = intval($close['close']);
            $settings[Site::OPT_CLOSETIPS] = $close['tips'];
            Site::saveSettings($settings);
            $this->success('操作成功');
            exit;
        }
        $entity = array();
        $entity['close'] = $settings[Site::OPT_CLOSE];
        $entity['tips'] = $settings[Site::OPT_CLOSETIPS];
        $this->assign('entity', $entity);
        C('FRAME_CURRENT', U('control/site/common'));
        $this->display();
    }
    
    public function updateAction() {
        $this->display();
    }
    
    public function flushAction() {
        if(IS_POST) {
            if(STORAGE_TYPE == 'File') {
                $this->file_rmdirs(dirname(RUNTIME_PATH));
            }
            $this->success('操作成功');
            exit;
        }
        $this->display();
    }
    
    private function file_rmdirs($path, $clean=false) {
        if(!is_dir($path)) {
            return false;
        }
        $files = glob($path . '/*');
        if($files) {
            foreach($files as $file) {
                is_dir($file) ? $this->file_rmdirs($file) : @unlink($file);
            }
        }
        return $clean ? true : @rmdir($path);
    }
    
    private function file_tree($path) {
        $files = array();
        $ds = glob($path . '/*');
        if(is_array($ds)) {
            foreach($ds as $entry) {
                if(is_file($entry)) {
                    $files[] = $entry;
                }
                if(is_dir($entry)) {
                    $rs = $this->file_tree($entry);
                    foreach($rs as $f) {
                        $files[] = $f;
                    }
                }
            }
        }
        return $files;
    }
    
    public function bomAction() {
        if(IS_POST) {
            $f = I('post.file');
            if(!empty($f)) {
                $fname = MB_ROOT . $f;
                if(is_file($fname)) {
                    $data = file_get_contents($fname);
                    $bom = substr($data, 0, 3);
                    if($bom == "\xEF\xBB\xBF") {
                        $data = substr($data, 3);
                        file_put_contents($fname, $data);
                        exit('success');
                    }
                }
                exit;
            }
            
            $path = MB_ROOT;
            $tree = $this->file_tree($path);
            $ds = array();
            foreach($tree as $t) {
                $t = str_replace($path, '', $t);
                $t = str_replace('\\', '/', $t);
                if(preg_match('/^.*\.php$/', $t)) {
                    $fname = $path . $t;
                    $fp = fopen($fname, 'r');
                    if(!empty($fp)) {
                        $bom = fread($fp, 3);
                        fclose($fp);
                        if($bom == "\xEF\xBB\xBF") {
                            $ds[] = $t;
                        }
                    }
                }
            }
            $this->assign('ds', $ds);
        }
        C('FRAME_CURRENT', U('control/site/flush'));
        $this->display();
    }
}