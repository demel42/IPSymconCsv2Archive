<?php

require_once __DIR__ . '/../libs/common.php';  // globale Funktionen

if (@constant('IPS_BASE') == null) {
    // --- BASE MESSAGE
    define('IPS_BASE', 10000);							// Base Message
    define('IPS_KERNELSHUTDOWN', IPS_BASE + 1);			// Pre Shutdown Message, Runlevel UNINIT Follows
    define('IPS_KERNELSTARTED', IPS_BASE + 2);			// Post Ready Message
    // --- KERNEL
    define('IPS_KERNELMESSAGE', IPS_BASE + 100);		// Kernel Message
    define('KR_CREATE', IPS_KERNELMESSAGE + 1);			// Kernel is beeing created
    define('KR_INIT', IPS_KERNELMESSAGE + 2);			// Kernel Components are beeing initialised, Modules loaded, Settings read
    define('KR_READY', IPS_KERNELMESSAGE + 3);			// Kernel is ready and running
    define('KR_UNINIT', IPS_KERNELMESSAGE + 4);			// Got Shutdown Message, unloading all stuff
    define('KR_SHUTDOWN', IPS_KERNELMESSAGE + 5);		// Uninit Complete, Destroying Kernel Inteface
    // --- KERNEL LOGMESSAGE
    define('IPS_LOGMESSAGE', IPS_BASE + 200);			// Logmessage Message
    define('KL_MESSAGE', IPS_LOGMESSAGE + 1);			// Normal Message
    define('KL_SUCCESS', IPS_LOGMESSAGE + 2);			// Success Message
    define('KL_NOTIFY', IPS_LOGMESSAGE + 3);			// Notiy about Changes
    define('KL_WARNING', IPS_LOGMESSAGE + 4);			// Warnings
    define('KL_ERROR', IPS_LOGMESSAGE + 5);				// Error Message
    define('KL_DEBUG', IPS_LOGMESSAGE + 6);				// Debug Informations + Script Results
    define('KL_CUSTOM', IPS_LOGMESSAGE + 7);			// User Message
}

if (!defined('vtBoolean')) {
    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
    define('vtArray', 8);
    define('vtObject', 9);
}

// Datumsformate

// Sekunden ab 01.01.1970 00:00:00
if (!defined('TSTAMP_FMT_UNIX')) {
    define('TSTAMP_FMT_UNIX', 0);
}
// 'YmdGis' (z.B. yyyymmddHHMMSS -> dd.mm.yyyy HH:MM:SS)
if (!defined('TSTAMP_FMT_LOG')) {
    define('TSTAMP_FMT_LOG', 1);
}
// 'Y-m-d H:i:s'
if (!defined('TSTAMP_FMT_ISO')) {
    define('TSTAMP_FMT_ISO', 2);
}
// 'd.m.Y H:i:s'
if (!defined('TSTAMP_FMT_DE')) {
    define('TSTAMP_FMT_DE', 3);
}
// 'Y/m/d H:i:s'
if (!defined('TSTAMP_FMT_EN')) {
    define('TSTAMP_FMT_EN', 4);
}

class Csv2Archive extends IPSModule
{
    use Csv2ArchiveCommon;

    public function Create()
    {
        parent::Create();
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->SetStatus(102);
    }

    public function GetConfigurationForm()
    {
        $formElements = [];

        $options = [];
        $options[] = ['label' => 'UNIX (seconds from 01.01.1970)', 'value' => TSTAMP_FMT_UNIX];
        $options[] = ['label' => 'Compact (yyyymmddHHMMSS)', 'value' => TSTAMP_FMT_LOG];
        $options[] = ['label' => 'ISO (yyyy-mm-dd HH:MM:SS)', 'value' => TSTAMP_FMT_ISO];
        $options[] = ['label' => 'German (dd.mm.yyyy HH:MM:SS)', 'value' => TSTAMP_FMT_DE];
        $options[] = ['label' => 'English (yyyy/mm/dd HH:MM:SS)', 'value' => TSTAMP_FMT_EN];

        $formActions = [];
        $formActions[] = ['type' => 'Label', 'label' => 'Attention: module only works with the web-console!'];
        $formActions[] = ['type' => 'Select', 'name' => 'tstamp_type', 'caption' => 'Timestamp format', 'options' => $options];
        $formActions[] = ['type' => 'ValidationTextBox', 'name' => 'delimiter', 'caption' => 'Delimiter'];
        $formActions[] = ['type' => 'IntervalBox', 'name' => 'tstamp_col', 'caption' => 'Column of timestamp'];
        $formActions[] = ['type' => 'IntervalBox', 'name' => 'value_col', 'caption' => 'Column of value'];
        $formActions[] = ['type' => 'CheckBox', 'name' => 'with_header', 'caption' => 'with header in 1st line'];
        $formActions[] = ['type' => 'CheckBox', 'name' => 'overwrite_old', 'caption' => 'overwrite old data'];
        $formActions[] = ['type' => 'CheckBox', 'name' => 'string_is_base64', 'caption' => 'value in csv ist base64-coded (string only)'];
        $formActions[] = ['type' => 'CheckBox', 'name' => 'do_reaggregate', 'caption' => 'reaggregate variable'];

        $formActions[] = ['type' => 'SelectVariable', 'name' => 'varID', 'caption' => 'Variable'];
        $formActions[] = ['type' => 'SelectFile', 'name' => 'data', 'caption' => 'CSV-Datei', 'extensions' => '.csv'];

        $formActions[] = [
                            'type'    => 'Button',
                            'caption' => 'Test Import',
                            'onClick' => 'Csv2Archive_Import($id, $tstamp_type, $delimiter, $with_header, $tstamp_col, $value_col, $overwrite_old, $string_is_base64, $do_reaggregate, $data, $varID, true);'
                        ];
        $formActions[] = [
                            'type'    => 'Button',
                            'caption' => 'Perform Import',
                            'onClick' => 'Csv2Archive_Import($id, $tstamp_type, $delimiter, $with_header, $tstamp_col, $value_col, $overwrite_old, $string_is_base64, $do_reaggregate, $data, $varID, false);'
                        ];

        $formActions[] = ['type' => 'Label', 'label' => '____________________________________________________________________________________________________'];
        $formActions[] = [
                            'type'    => 'Button',
                            'caption' => 'Module description',
                            'onClick' => 'echo "https://github.com/demel42/IPSymconCsv2Archive/blob/master/README.md";'
                        ];

        $formStatus = [];
        $formStatus[] = ['code' => '101', 'icon' => 'inactive', 'caption' => 'Instance getting created'];
        $formStatus[] = ['code' => '102', 'icon' => 'active', 'caption' => 'Instance is active'];
        $formStatus[] = ['code' => '104', 'icon' => 'inactive', 'caption' => 'Instance is inactive'];

        return json_encode(['elements' => $formElements, 'actions' => $formActions, 'status' => $formStatus]);
    }

    public function Import(int $tstamp_type, string $delimiter, bool $with_header, int $tstamp_col, int $value_col, bool $overwrite_old, bool $string_is_base64, bool $do_reaggregate, string $data, int $varID, bool $test_mode)
    {
        $b = 'parameter: ';
        $b .= 'tstamp_type=' . $tstamp_type . ', ';
        $b .= 'delimiter="' . $delimiter . '"' . ', ';
        $b .= 'with_header=' . $this->bool2str($with_header) . ', ';
        $b .= 'tstamp_col=' . $tstamp_col . ', ';
        $b .= 'value_col=' . $value_col . ', ';
        $b .= 'overwrite_old=' . $this->bool2str($overwrite_old) . ', ';
        $b .= 'string_is_base64=' . $this->bool2str($string_is_base64) . ', ';
        $b .= 'do_reaggregate=' . $this->bool2str($do_reaggregate) . ', ';
        $b .= 'test_mode=' . $this->bool2str($test_mode) . ', ';
        $b .= 'varID=' . $tstamp_type . '(' . IPS_GetName($varID) . ')' . ', ';
        $b .= 'length of data=' . strlen($data) . ' bytes' . ', ';
        $this->SendDebug(__FUNCTION__, $b, 0);

        if ($test_mode) {
            echo '***' . $this->Translate('testmode') . '***' . "\n\n";
        }

        if (!$tstamp_col || !$value_col || $tstamp_col == $value_col) {
            echo $this->Translate('column-number(s) are erroneous') . "\n";
            return;
        }

		$delimiter = stripcslashes($delimiter);
        if (strlen($delimiter) != 1) {
            echo $this->Translate('column-delimiter must be a single character') . "\n";
            return;
        }

		$data = base64_decode($data);
        if (!strlen($data)) {
            echo $this->Translate('no data') . "\n";
            return;
        }

        if ($varID == '' || $varID == 0) {
            echo $this->Translate('no variable given') . "\n";
            return;
        }

        $r = IPS_GetVariable($varID);
        if ($r == false) {
            echo $this->Translate('variable not found') . "\n";
            return;
        }
        $value_dtype = $r['VariableType'];

        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        if (AC_GetLoggingStatus($archiveID, $varID) && AC_GetAggregationType($archiveID, $varID) == 1) {
            echo $this->Translate('variable has no standard-aggregation') . "\n";
            return;
        }

        switch ($tstamp_type) {
            case TSTAMP_FMT_UNIX:
                $ts_format = 'U';
                break;
            case TSTAMP_FMT_LOG:
                $ts_format = 'YmdGis';
                break;
            case TSTAMP_FMT_ISO:
                $ts_format = 'Y-m-d H:i:s';
                break;
            case TSTAMP_FMT_DE:
                $ts_format = 'd.m.Y H:i:s';
                break;
            case TSTAMP_FMT_EN:
                $ts_format = 'Y/m/d H:i:s';
                break;
            default:
                echo $this->Translate('invalid timestamp-format') . "\n";
                return;
        }

        $tstamp_col--;
        $value_col--;
        $min_cols = max($tstamp_col, $value_col) + 1;

        $min_tstamp = mktime(0, 0, 0, 1, 1, 2000);
        $max_tstamp = time();

        $rows = explode("\n", $data);

        $this->SendDebug(__FUNCTION__, 'rows=' . print_r($rows, true), 0);

        $n_row = 0;
        $errors = [];
        $values = [];
        $tstamp_map = [];
echo "rows=" . print_r($rows, true) . "\n";
        foreach ($rows as $row) {
            $n_row++;
echo "n_row=$n_row, row=$row\n";
            if ($with_header && $n_row === 1) {
                continue;
            }
            if ($row === '') {
                continue;
            }
echo "row=$row, delimiter=$delimiter\n";
            $fields = str_getcsv($row, $delimiter);
echo "fields=" . print_r($fields, true) . "\n";
            $n_fields = count($fields);
            if ($n_fields < $min_cols) {
                $errors[] = ['row' => $n_row, 'msg' => $this->Translate('not enough cols')];
                continue;
            }

            $tstamp_s = $fields[$tstamp_col];
            $value_s = $fields[$value_col];

            $tm = date_create_from_format($ts_format, $tstamp_s);
            if (!$tm) {
                $e = 'not a valid timestamp';
                $this->SendDebug(__FUNCTION__, 'err=' . $e . ', n_row=' . $n_row . ', fields=' . print_r($fields, true), 0);
                $errors[] = ['row' => $n_row, 'msg' => $this->Translate($e)];
                continue;
            }
            $tstamp = $tm->format('U');
            if ($tstamp < $min_tstamp) {
                $e = 'timestamp is too old';
                $this->SendDebug(__FUNCTION__, 'err=' . $e . ', n_row=' . $n_row . ', fields=' . print_r($fields, true), 0);
                $errors[] = ['row' => $n_row, 'msg' => $this->Translate($e)];
                continue;
            }
            if ($tstamp > $max_tstamp) {
                $e = 'timestamp is in the future';
                $this->SendDebug(__FUNCTION__, 'err=' . $e . ', n_row=' . $n_row . ', fields=' . print_r($fields, true), 0);
                $errors[] = ['row' => $n_row, 'msg' => $this->Translate($e)];
                continue;
            }
            if (in_array($tstamp, $tstamp_map)) {
                $e = 'duplicate timestamp';
                $this->SendDebug(__FUNCTION__, 'err=' . $e . ', n_row=' . $n_row . ', fields=' . print_r($fields, true), 0);
                $errors[] = ['row' => $n_row, 'msg' => $this->Translate($e)];
                continue;
            }
            $tstamp_map[] = $tstamp;

            switch ($value_dtype) {
                case vtBoolean:
                    $ok = false;
                    if (is_bool($value_s)) {
                        $b = boolval($s);
                        $ok = true;
                    } elseif (in_array(strtolower($value_s), ['1', 'ja', 'yes', 'true'])) {
                        $ok = true;
                        $b = true;
                    } elseif (in_array(strtolower($value_s), ['0', 'nein', 'no', 'false'])) {
                        $ok = true;
                        $b = false;
                    }
                    if (!$ok) {
                        $e = 'value is invalid';
                        $this->SendDebug(__FUNCTION__, 'err=' . $e . ', n_row=' . $n_row . ', fields=' . print_r($fields, true), 0);
                        $errors[] = ['row' => $n_row, 'msg' => $this->Translate($e)];
                        continue;
                    }
                    $value = $b ? '1' : '0';
                    break;
                case vtInteger:
                    if (!preg_match('|^[-+]?[0-9]+$|', $value_s)) {
                        $e = 'value is invalid';
                        $this->SendDebug(__FUNCTION__, 'err=' . $e . ', n_row=' . $n_row . ', fields=' . print_r($fields, true), 0);
                        $errors[] = ['row' => $n_row, 'msg' => $this->Translate($e)];
                        continue;
                    }
                    $value = intval($value_s);
                    break;
                case vtFloat:
                    if (!preg_match('|[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?|', $value_s)) {
                        $e = 'value is invalid';
                        $this->SendDebug(__FUNCTION__, 'err=' . $e . ', n_row=' . $n_row . ', fields=' . print_r($fields, true), 0);
                        $errors[] = ['row' => $n_row, 'msg' => $this->Translate($e)];
                        continue;
                    }
                    $f = floatval($value_s);
                    $d = strlen($f) - strlen(floor($f));
                    if ($d) {
                        $d--;
                    }
                    $value = number_format($f, $d, '.', '');
                    break;
                case vtString:
                    $value = $string_is_base64 ? $value_s : base64_encode($value_s);
                    break;
            }
            $values[$tstamp] = $value;
        }

        $this->SendDebug(__FUNCTION__, 'n_values=' . count($values), 0);

        echo $this->Translate('number of data-rows') . ': ' . count($values) . "\n\n";

        if (count($errors)) {
            $b = $this->Translate('found errors') . "\n";
            foreach ($errors as $error) {
                $row = $error['row'];
                $msg = $error['msg'];
                $b .= '  ' . $this->Translate('Row') . ' ' . $row . ': ' . $msg . "\n";
            }
            echo $b;
            return;
        }

        ksort($values, SORT_NUMERIC);

        $y = '';
        $m = '';
        $new_values = [];
        $new_tstamp_map = [];
        $need_reaggregate = false;
        $n_inserted = 0;
        $n_updated = 0;
        $total_files = 0;
        $total_inserted = 0;
        $total_updated = 0;
        foreach ($values as $tstamp => $value) {
            $this->SendDebug(__FUNCTION__, ' ... tstamp=' . $tstamp . ' / ' . date('d.m.Y H:i:s', $tstamp) . ', value=' . $value, 0);

            $_y = date('Y', $tstamp);
            $_m = date('m', $tstamp);
            if ($_y != $y || $_m != $m) {
                if (count($new_values) > 0) {
                    $total_files++;

                    $s = file_exists($fname) ? 'update file' : 'create file';
                    $b = ' - '
                        . $this->Translate($s)
                        . ' '
                        . $fname
                        . ': '
                        . $this->Translate('items inserted')
                        . '='
                        . $n_inserted
                        . ', '
                        . $this->Translate('items updated')
                        . '='
                        . $n_updated;

                    if (!$test_mode) {
                        ksort($new_values, SORT_NUMERIC);

                        $buf = '';
                        foreach ($new_values as $new_tstamp => $new_value) {
                            $buf .= $new_tstamp . ',' . $new_value . "\n";
                        }

                        $tmp_fname = $fname . '~';
                        $fp = fopen($tmp_fname, 'w');
                        $ok = true;
                        if (!$fp) {
                            echo $this->Translate('unable to create file') . ' ' . $tmp_fname . "\n";
                            $ok = false;
                        }
                        if ($ok && !fwrite($fp, $buf)) {
                            echo $this->Translate('unable to write to file') . ' ' . $tmp_fname . ' (' . strlen($buf) . ' bytes)' . "\n";
                            $ok = false;
                        }
                        if ($ok && !fclose($fp)) {
                            echo $this->Translate('unable to close file') . ' ' . $tmp_fname . "\n";
                            $ok = false;
                        }
                        if ($ok && file_exists($fname) && !unlink($fname)) {
                            echo $this->Translate('unable to delete file') . ' ' . $fname . "\n";
                            $ok = false;
                        }
                        if ($ok && !rename($tmp_fname, $fname)) {
                            echo $this->Translate('unable to rename file') . ' ' . $tmp_fname . "\n";
                            $ok = false;
                        }
                        if ($ok) {
                            $need_reaggregate = true;
                            $b .= ' => ' . $this->Translate('ok');
                        } else {
                            $b .= ' => ' . $this->Translate('failed');
                        }
                    }
                    echo $b . "\n";
                    $this->SendDebug(__FUNCTION__, $b, 0);
                }

                $new_values = [];
                $new_tstamp_map = [];
                $n_inserted = 0;
                $n_updated = 0;

                $y = $_y;
                $m = $_m;

                $fname = IPS_GetKernelDir() . 'db' . DIRECTORY_SEPARATOR . $y;
                if (!file_exists($fname)) {
                    if (!mkdir($fname)) {
                        echo $this->Translate('unable to create directory') . ' ' . $fname . "\n";
                        return;
                    }
                } elseif (!is_dir($fname)) {
                    echo $fname . ' ' . $this->Translate('is not a directory') . "\n";
                    return;
                }
                $fname .= DIRECTORY_SEPARATOR . $m;
                if (!file_exists($fname)) {
                    if (!mkdir($fname)) {
                        echo $this->Translate('unable to create directory') . ' ' . $fname . "\n";
                        return;
                    }
                } elseif (!is_dir($fname)) {
                    echo $fname . ' ' . $this->Translate('is not a directory') . "\n";
                    return;
                }
                $fname .= DIRECTORY_SEPARATOR . $varID . '.csv';
                if (file_exists($fname)) {
                    $data = file_get_contents($fname);
                    $rows = explode("\n", $data);
                    foreach ($rows as $row) {
                        if ($row == '') {
                            continue;
                        }
                        $fields = str_getcsv($row, ',');
                        $new_tstamp = $fields[0];
                        $new_value = $fields[1];
                        $new_values[$new_tstamp] = $new_value;
                        $new_tstamp_map[] = $new_tstamp;
                    }
                }
            }
            if (in_array($tstamp, $new_tstamp_map)) {
                if (!$overwrite_old) {
                    $errors[] = ['row' => $n_row, 'msg' => 'timestamp exists in archive'];
                    continue;
                }
                $n_updated++;
                $total_updated++;
            } else {
                $n_inserted++;
                $total_inserted++;
            }
            $new_values[$tstamp] = $value;
        }

        if (count($new_values) > 0) {
            $total_files++;

            $s = file_exists($fname) ? 'update file' : 'create file';
            $b = ' - '
                 . $this->Translate($s)
                 . ' '
                 . $fname
                 . ': '
                 . $this->Translate('items inserted')
                 . '='
                 . $n_inserted
                 . ', '
                 . $this->Translate('items updated')
                 . '='
                 . $n_updated;

            if (!$test_mode) {
                ksort($new_values, SORT_NUMERIC);

                $buf = '';
                foreach ($new_values as $new_tstamp => $new_value) {
                    $buf .= $new_tstamp . ',' . $new_value . "\n";
                }

                $tmp_fname = $fname . '~';
                $fp = fopen($tmp_fname, 'w');
                $ok = true;
                if (!$fp) {
                    echo $this->Translate('unable to create file') . ' ' . $tmp_fname . "\n";
                    $ok = false;
                }
                if ($ok && !fwrite($fp, $buf)) {
                    echo $this->Translate('unable to write to file') . ' ' . $tmp_fname . ' (' . strlen($buf) . ' bytes)' . "\n";
                    $ok = false;
                }
                if ($ok && !fclose($fp)) {
                    echo $this->Translate('unable to close file') . ' ' . $tmp_fname . "\n";
                    $ok = false;
                }
                if ($ok && file_exists($fname) && !unlink($fname)) {
                    echo $this->Translate('unable to delete file') . ' ' . $fname . "\n";
                    $ok = false;
                }
                if ($ok && !rename($tmp_fname, $fname)) {
                    echo $this->Translate('unable to rename file') . ' ' . $tmp_fname . "\n";
                    $ok = false;
                }
                if ($ok) {
                    $need_reaggregate = true;
                    $b .= ' => ' . $this->Translate('ok');
                } else {
                    $b .= ' => ' . $this->Translate('failed');
                }
            }
            echo $b . "\n";
            $this->SendDebug(__FUNCTION__, $b, 0);
        }

        echo "\n";
        echo $this->Translate('processed files')
                . '='
                . $total_files
                . ', '
                . $this->Translate('items inserted')
                . '='
                . $total_inserted
                . ', '
                . $this->Translate('items updated')
                . '='
                . $total_updated
                . "\n";

        if ($do_reaggregate && $need_reaggregate) {
            $this->SendDebug(__FUNCTION__, 're-aggregate', 0);
            AC_ReAggregateVariable($archiveID, $varID);
        }
    }
}
