#!/usr/bin/php -q
<?php
    function recursive_remove ($base) {
        // Apagar recursivamente uma pasta
        clearstatcache ();
        $retval = true;
        @ $dd = opendir ($base);
        if ($dd) {
            while (($d_entry = readdir ($dd)) !== false) {
                if ($d_entry != "." && $d_entry != "..") {
                    $fpath = $base . "/" . $d_entry;
                    if (is_dir ($fpath)) {
                        if (! recursive_remove ($fpath)) {
                            $retval = false;
                        }
                    } else {
                        if (unlink ($fpath)) {
                            echo ("\nFalha ao excluir o arquivo '" . $fpath . "'!\n");
                            $retval = false;
                        }
                    }
                }
            }
            closedir ($dd);
        }
        if (! rmdir ($base)) {
            echo ("\nFalha ao excluir a pasta '" . $base . "'!\n");
            $retval = false;
        }
        return ($retval);
    }

    function trycopyfile ($orig, $dest) {
        // Copia um arquivo para outro.
        clearstatcache ();
        echo (".");
        if (copy ($orig, $dest)) {
            $prm = fileperms ($dest);
            if ($prm !== false) {
                $prm_n = $prm | 0600;
                if ($prm_n != $prm) {
                    chmod ($dest, $prm_n);
                }
            }
            return (true);
        } else {
            echo ("\nFalha ao copiar '" . $orig . "' para '" . $dest . "'!\n");
            return (false);
        }
    }

    function trycopy ($baseorig, $basedest) {
        $retval = true;
        clearstatcache ();
        if (is_dir ($baseorig)) {
            if (is_dir ($basedest)) {
                $dd = opendir ($baseorig);
                if ($dd) {
                    while (($d_entry = readdir ($dd)) !== false) {
                        if ($d_entry != "." && $d_entry != "..") {
                            $fbo = $baseorig . "/" . $d_entry;
                            $fbd = $basedest . "/" . $d_entry;
                            echo (".");
                            if (! trycopy ($fbo, $fbd)) {
                                $retval = false;
                            }
                        }
                    }
                    closedir ($dd);
                } else {
                    echo ("\nFalha ao abrir a pasta '" . $baseorig . "'!\n");
                    $retval = false;
                }
            } else {
                @ unlink ($basedest);
                if (mkdir ($basedest)) {
                    $prm = fileperms ($basedest);
                    if ($prm !== false) {
                        $prm_n = $prm | 0700;
                        if ($prm_n != $prm) {
                            chmod ($basedest, $prm_n);
                        }
                    }
                    $retval = trycopy ($baseorig, $basedest);
                } else {
                    echo ("\nFalha ao copiar a pasta '" . $baseorig . "'!\n");
                    $retval = false;
                }
            }
        } else {
            // Origem é um arquivo. Se o destino for uma pasta, remover recursivamente
            if (is_dir ($basedest)) {
                if (recursive_remove ($basedest)) {
                    $retval = trycopyfile ($baseorig, $basedest);
                } else {
                    echo ("\nFalha ao copiar '" . $baseorig . "' para '" . $basedest . "'!\n");
                    $retval = false;
                }
            } else {
                $flag = true;
                $f1 = filesize ($baseorig);
                if ($f1 && file_exists ($basedest)) {
                    $f2 = filesize ($basedest);
                    if ($f2 == $f1) {
                        $flag = false;
                    }
                }
                if ($flag) {
                    $retval = trycopyfile ($baseorig, $basedest);
                }
            }
        }
        return ($retval);
    }

    echo (" **** Programa para copiar arvores de pasta para um destino, desde que os arquivos nao existam. ****\n");
    if ($_SERVER['argc'] < 3) {
        echo ("Uso: " . $_SERVER['argv'][0] . " <origem> [<origem [<origem> [...]]] <destino>\n");
        exit (1);
    } else {
        $status = true;
        for ($i = 1; $i < $_SERVER['argc'] - 1; $i++) {
            if (! trycopy ($_SERVER['argv'][$i], $_SERVER['argv'][$_SERVER['argc'] - 1])) {
                $status = false;
            }
        }
        if ($status) {
            echo ("\n OK!\n");
            exit (0);
        } else {
            echo ("\nA copia de um ou mais itens falhou!\n");
            exit (1);
        }
    }
?>
