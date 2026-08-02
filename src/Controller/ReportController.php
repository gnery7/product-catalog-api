<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Service\CompanyService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ReportController
{
    private ProductService $productService;
    private CompanyService $companyService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->companyService = new CompanyService();
    }

    public function generate(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $actionLabels = [
            'create' => 'Criação',
            'update' => 'Atualização',
            'delete' => 'Remoção',
        ];

        $stm = $this->productService->getAll($adminUserId);
        $fetchedProducts = $stm->fetchAll();

        $products = [];
        foreach ($fetchedProducts as $fetchedProduct) {
            $categoryTitle = $fetchedProduct->category_title;
            unset($fetchedProduct->category_title);
            if (!isset($products[$fetchedProduct->id])) {
                $fetchedProduct->categories = [];
                $products[$fetchedProduct->id] = $fetchedProduct;
            }
            $products[$fetchedProduct->id]->categories[] = $categoryTitle;
        }

        $data = [];
        $data[] = [
            'Id do produto',
            'Nome da Empresa',
            'Nome do Produto',
            'Valor do Produto',
            'Categorias do Produto',
            'Data de Criação',
            'Logs de Alterações'
        ];

        foreach ($products as $product) {
            $stm = $this->companyService->getNameById($product->company_id);
            $companyName = $stm->fetch()->name;

            $stm = $this->productService->getLog($product->id);
            $productLogs = $stm->fetchAll();

            $formattedLogs = [];
            foreach ($productLogs as $productLog) {
                $userName = $productLog->admin_user_name ?? 'usuario desconhecido';
                $actionLabel = $actionLabels[$productLog->action] ?? $productLog->action;
                $logDate = date('d/m/Y H:i:s', strtotime($productLog->timestamp));
                $formattedLogs[] = "({$userName}, {$actionLabel}, {$logDate})";
            }

            $row = [];
            $row[] = $product->id;
            $row[] = $companyName;
            $row[] = $product->title;
            $row[] = $product->price;
            $row[] = implode(', ', $product->categories);
            $row[] = $product->created_at;
            $row[] = implode(',<br>', $formattedLogs);
            $data[] = $row;
        }

        $report = "<table style='font-size: 10px;'>";
        foreach ($data as $row) {
            $report .= "<tr>";
            foreach ($row as $column) {
                $report .= "<td>{$column}</td>";
            }
            $report .= "</tr>";
        }
        $report .= "</table>";

        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
