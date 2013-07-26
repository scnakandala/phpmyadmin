<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for correctness of functions in libraries/sql.lib.php
 *
 * @package PhpMyAdmin-test
 */
/*
 * Include to test.
 */
require_once 'libraries/Util.class.php';
require_once 'libraries/sql.lib.php';
require_once 'libraries/php-gettext/gettext.inc';
require_once 'libraries/database_interface.inc.php';
require_once 'libraries/relation.lib.php';
require_once 'libraries/sqlparser.lib.php';

/**
 * tests for methods under libraries/sql.lib.php
 *
 * @package PhpMyAdmin-test
 */
class PMA_SQLLib_Test extends PHPUnit_Framework_TestCase {

    /**
     * Setup function for test cases
     * 
     * @return void
     */
    public function setUp() {
        $_REQUEST['printview'] = '1';
        
        $GLOBALS['server'] = 1;
    }

    /**
     * Test case for PMA_getHtmlForPrintButton
     * 
     * @return void
     */
    public function testPMAGetHtmlForPrintButton() {
        $dbi = $this->getMockBuilder('PMA_DatabaseInterface')
                ->disableOriginalConstructor()
                ->getMock();
        
        $dbi->expects($this->any())->method('isSuperuser')
                ->will($this->returnValue(true));
        
        $GLOBALS['dbi'] = $dbi;
        
        $html = PMA_getHtmlForPrintButton();
        $this->assertEquals(
                '<p class="print_ignore"><input type="button" class="button"'
                . ' id="print" value="Print" /></p>', $html
        );
    }

    /**
     * Test case for PMA_getNewDatabase
     * 
     * @return void
     */
    public function testPMAGetNewDatabase() {
        $sql = 'use testdb2';
        $databases = array(
            array('SCHEMA_NAME' => 'testdb1'),
            array('SCHEMA_NAME' => 'testdb2'),
            array('SCHEMA_NAME' => 'testdb3')
        );
        $db = PMA_getNewDatabase($sql, $databases);
        $this->assertEquals(array('SCHEMA_NAME' => 'testdb2'), $db);
    }

    /**
     * Test case for PMA_getTableNameBySQL
     * 
     * @return void
     */
    public function testPMAGetTableNameBySql() {
        $sql = 'select col1 from table1, table2 where table1.col1 = table2.col2';
        $tables = array('table1', 'table2', 'table3');
        $results = PMA_getTableNameBySQL($sql, $tables);
        $this->assertEquals('table1', $results);
    }

    /**
     * Test case for PMA_getHtmlForRelationalColumnDropdown
     * 
     * @return void
     */
    public function testGetHtmlForRelationalColumnDropdown() {
        $dbi = $this->getMockBuilder('PMA_DatabaseInterface')
                ->disableOriginalConstructor()
                ->getMock();
        
        $show_create_table_1 = "CREATE TABLE `pma_bookmark` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `dbase` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
        `user` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
        `label` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
        `query` text COLLATE utf8_bin NOT NULL,
        PRIMARY KEY (`id`),
        KEY `foreign_field` (`foreign_db`,`foreign_table`)
        ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks'";

        $dbi->expects($this->at(1))->method('fetchValue')
                ->will($this->returnValue($show_create_table_1));
        
        $GLOBALS['dbi'] = $dbi;
        
        $html = PMA_getHtmlForRelationalColumnDropdown(
                'pma', 'pma_bookmark', 'foreign_field', 'curr_value'
        );

        $regexp = '{"dropdown":"<span class=\"curr_value\">.*<\/span><a href=\"'
                . 'browse_foreigners.php?db=.*&amp;table=,*&amp;field=.*&amp;token=.*\"'
                . ' target=\"_blank\" class=\"browse_foreign\" >Browse foreign'
                . ' values<\/a>","message":"<a class=\"hide\" '
                . 'id=\"update_recent_tables\" href=\"index.php?ajax_request=1&amp;'
                . 'recent_table=1&amp;token=.*\"><\/a>","success":true}';

        $this->assertRegExp($regexp, $html);
    }
}
?>
