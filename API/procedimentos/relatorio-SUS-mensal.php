<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
header("Access-Control-Allow-Credentials: true");

require_once '../db.php';   // Importa o arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Data = json_decode(file_get_contents('php://input'), true);
    $ano = $Data['ano'];
    $mes = $Data['mes']; // 'mes' contains the selected month value (1 to 12)

    // Determine the start and end dates for the specified month
    $startDate = date("$ano-$mes-01");
    $endDate = date("Y-m-t", strtotime($startDate));

    // Construct the SQL query
    $query = "SELECT * FROM historicoatendimentos WHERE YEAR(data) = $ano AND MONTH(data) = $mes";

    $result = db($query);

    if (count($result) === 0) {
        echo json_encode(array("error" => "Nenhum procedimento SUS registrado."));
    } else {
        $codSusArray = [];

        // Flatten the 'codSus' arrays
        foreach ($result as $row) {
            $codSusArray = array_merge($codSusArray, json_decode($row['codSus'], true));
        }

        // Count occurrences of individual values
        $codSusCounts = array_count_values($codSusArray);

        // Retrieve 'nome' for each 'codSus' from procedimentos_sus table
        $codSusWithNames = [];
        foreach ($codSusCounts as $codSus => $count) {
            $query = "SELECT nome FROM procedimentos_sus WHERE cod_sus = '$codSus'";
            $nameResult = db($query);
            $nome = $nameResult[0]['nome']; // Assuming 'nome' is the field name
            $codSusWithNames[$codSus] = array("codSus" => $codSus, "count" => $count, "nome" => $nome);
        }

        $response = array(
            'codSus' => $codSusWithNames,
        );

        echo json_encode($response);
    }
} else {
    echo json_encode(array("message" => "Método inválido"));
}

?>
