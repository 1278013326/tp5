<?php


namespace app\peng\controller;

use think\Controller;
use think\Request;

class Excel extends Controller
{
    public function __construct(Request $request = null)
    {
        import('phpexcel/PHPExcel', EXTEND_PATH,'.php');
        parent::__construct($request);
    }

    /**
     * excel导入数据
     */
    public function setExcelData() {
        $data = array('0'=>array(1,2,3),'1'=>array(4,5,6));
        $filter = array('姓名','年龄','身高');
        $filename = 'aa'; //文件名

        $objPHPExcel = new \PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        // Add some data
        $letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        if($data){
            $i = 1;
            $j = 0;
            foreach ($filter as $field => $title) {
                $index = $letter[$j].$i;
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($index, $title);
                $j++;
            }
            $i++;
            foreach ($data as $key => $value) {
                $j = 0;
                foreach ($filter as $k=> $val) {
                    $index = $letter[$j].$i;
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($index, $value[$k]);
                    $j++;
                }
                $i++;
            }
        }
        else{
            die;
        }
        $date = date('Y-m-d',time());
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($filename);
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.$date.'.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
//
//        exit();
    }

    /**
     * excel导出数据
     */
    public function getExcelData() {
        $filename =  CORE_PATH . 'aa2019-11-08 .xlsx';
//        $objPHPExcel = new \PHPExcel();
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        $data = $objPHPExcel->getSheet(0)->toArray();
        print_r($data);die;
//        return $data;
    }

    /**
     * csv导入数据
     */
    public function setCsvData() {
        $data = array('0'=>array(1,2,3),'1'=>array(4,5,6));
        $filter = array('姓名','年龄','身高');
        $filename = 'aa'; //文件名
        /* 输入到CSV文件 */
        $html = "\xEF\xBB\xBF";
        //表头
        foreach ($filter as $key => $title) {
            $html .= $title . "\t,";
        }
        $html .= "\n";
        //表身
        $date = date('Y-m-d',time());
        foreach ($data as $k => $v) {
            foreach ($filter as $key => $title) {
                $html .= $v[$key] . "\t, ";
            }
            $html .= "\n";
        }
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename=".$filename.$date.".csv");
        echo $html;
        die;
    }

    /**
     * csv导入数据
     */
    public function getCsvData() {
        $filename =  CORE_PATH . 'aa2019-11-08.csv';
        $handle = fopen($filename,'r');
        $arr = array();
        $data = array();
        //循环取出 文件中数据
        while($data = fgetcsv($handle)){
            $arr[]=$data;
        }
        print_r($arr);die;
//        return $arr;
    }
}