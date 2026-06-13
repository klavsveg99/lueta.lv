<?php
namespace App;

class SupabaseClient
{
    private $url;
    private $serviceKey;
    private $anonKey;

    public function __construct($url, $serviceKey, $anonKey)
    {
        $this->url = rtrim($url, '/');
        $this->serviceKey = $serviceKey;
        $this->anonKey = $anonKey;
    }

    private function request($method, $endpoint, $data = null, $useServiceKey = true)
    {
        if (!function_exists('curl_init')) {
            return array('error' => 'cURL extension is not enabled');
        }

        $ch = curl_init();
        $headers = array(
            'apikey: ' . ($useServiceKey ? $this->serviceKey : $this->anonKey),
            'Content-Type: application/json',
        );
        if ($useServiceKey) {
            $headers[] = 'Authorization: Bearer ' . $this->serviceKey;
        }

        $url = $this->url . '/rest/v1/' . $endpoint;
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CUSTOMREQUEST => $method,
        ));

        if ($data !== null && in_array($method, array('POST', 'PATCH', 'PUT'))) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return array('error' => $error);
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $decoded = json_decode($response, true);
            return $decoded !== null ? $decoded : array();
        }

        return array('error' => "HTTP $httpCode: " . substr($response, 0, 500));
    }

    public function select($table, $params = array())
    {
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return $this->request('GET', $table . ($query ? '?' . $query : ''));
    }

    public function insert($table, $data)
    {
        $result = $this->request('POST', $table, $data);
        if ($result === null) return null;
        if (isset($result['error'])) return $result;
        return $result;
    }

    public function update($table, $data, $where)
    {
        $query = http_build_query($where, '', '&', PHP_QUERY_RFC3986);
        return $this->request('PATCH', $table . '?' . $query, $data);
    }

    public function delete($table, $where)
    {
        $query = http_build_query($where, '', '&', PHP_QUERY_RFC3986);
        return $this->request('DELETE', $table . '?' . $query);
    }

    public function upsert($table, $data)
    {
        return $this->request('POST', $table . '?on_conflict=id', $data);
    }

    public function getContentBlocks($page = 'index')
    {
        $result = $this->select('content_blocks', array(
            'page' => 'eq.' . $page,
            'select' => 'section,block_key,block_value',
        ));
        if ($result === null || isset($result['error'])) return array();
        $blocks = array();
        foreach ($result as $row) {
            $key = $row['block_key'];
            $blocks[$key] = $row['block_value'];
        }
        return $blocks;
    }
}
