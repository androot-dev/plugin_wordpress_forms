<?php

class ScrappingService
{
    /* clase para gestionar la conexion con la pagina de https://www.uscis.gov/ */

    public function __construct($url)
    {
        $this->urlforms = $url;
        $this->content = file_get_contents($this->urlforms);
        $this->response = [];
        try {
            libxml_use_internal_errors(true);
            libxml_clear_errors();
            $this->dom =  new DOMDocument();
            $this->dom->validateOnParse = true;
            $this->dom->loadHTML($this->content);
            libxml_clear_errors();
        } catch (Exception $e) {
            //...
        }
        $this->countProps = 0;
    }
    public function getTexts($search, $ends)
    {
        $content = $this->content;
        $res = [];
        while (strpos($content, $search)) {
            $temp_content = $content;
            $pos = strpos($temp_content, $search);
            //seleccionar el proximo "</a>" despues de $pos
            $pos2 = strpos($temp_content, $ends, $pos);
            //ahora obtener el contenido entre el final de pos y el inicio de </a> y borrar el resto
            $text = substr($temp_content, $pos, $pos2 - $pos);
            //eliminar el resto al inicio
            $text = str_replace($search, "", $text);
            $res[] = $text;
            //eliminar de content search hasta </a>
            $content = substr($content, $pos2);
        }
        $this->response = $res;
        return $this;
    }
    public function getNode($query, $param = "nodeValue")
    {
        $content = $this->content;
        $res = [];
        $xpath = new DOMXPath($this->dom);
        $links = $xpath->query($query);
        foreach ($links as $link) {
            $res[] = $link->$param;
        }

        $this->response[$this->countProps] = $res;
        $this->countProps++;
        return $this;
    }
    public function getAttribute($query, $attr = "href")
    {
        $content = $this->content;
        $res = [];
        $xpath = new DOMXPath($this->dom);
        $links = $xpath->query($query);
        foreach ($links as $link) {
            $res[] = $link->getAttribute($attr);
        }

        $this->response[$this->countProps] = $res;
        $this->countProps++;
        return $this;
    }
    public function clean($callback = null)
    {
        if (!is_callable($callback)) {
            $this->response = [];
            $this->countProps = 0;
        } else {
            array_walk($this->response, $callback);
        }

        return $this;
    }
    public function quickClean($keys)
    {
        $new_response = [];
        for ($i = 0; $i < count($this->response) - 1; $i += 2) {
            $cols = count($this->response[$i]);
            $rows = count($this->response[$i + 1]);

            if ($cols == $rows) {
                $new_response = array_merge($new_response, array_map(function ($url, $name) use ($keys) {
                    return array_combine($keys, [$url, $name]);
                }, $this->response[$i], $this->response[$i + 1]));
            } else {
                error_log("Arrays no tienen la misma longitud");
            }
        }
        $this->response = $new_response;

        return $this;
    }
    public function find($data)
    {
        $cols = [];
        foreach ($data as $key => $value) {
            $cols[] = $key;
            $search = $value["search"];
            $xpath = $value["xpath"];
            foreach ($search as $key2 => $value2) {
                $this->search([$key2 . ":" . $value2 => $xpath]);
            }
        }


        return $this;
    }
    public static function checkurl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            error_log("URL no es válida");
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200) {
            return true;
        } else {
            return false;
        }
    }
    public function link()
    {
        $equals_length = [];
        foreach ($this->response as $key => $value) {
            $equals_length[] = count($value);
        }
        if (count(array_unique($equals_length)) == 1) {
            //fusionar los arrays
            $props = [];
            //recorrer cada array y pasar la key y los valores dentro del array props
            foreach ($this->response as $key => $value) {
                foreach ($value as $key2 => $value2) {

                    if (!is_array($value2)) {
                        //si no es un array, crearlo y agregar el valor
                        $props[$key2] = $value2;
                    } else {
                        //scar las propiedades del array e insertarlas en el array props
                        foreach ($value2 as $key3 => $value3) {
                            $props[$key2][$key3] = $value3;
                        }
                    }
                }
            }
            $this->response = $props;
        } else {
            error_log("Arrays no tienen la misma longitud");
        }
        return $this;
    }
    public function search($array)
    {
        foreach ($array as $key => $value) {

            $params = explode(":", $key);

            switch ($params[0]) {
                case 'attr':
                    $this->getAttribute($value, $params[1]);
                    break;
                case 'node':
                    $this->getNode($value, $params[1]);
                    break;

                default:
                    error_log("Error en el parametro de busqueda");
                    break;
            }
        }
        return $this;
    }

    public function get()
    {

        return $this->response;
    }
}