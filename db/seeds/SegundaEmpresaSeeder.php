<?php

use Phinx\Seed\AbstractSeed;

class SegundaEmpresaSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('company')->insert([
            ['name' => 'Empresa 2', 'active' => 1],
        ])->saveData();
        $companyId = $this->getAdapter()->getConnection()->lastInsertId();

        $this->table('admin_user')->insert([
            ['company_id' => $companyId, 'email' => 'admin@empresa2.com', 'name' => 'admin empresa 2'],
        ])->saveData();
        $adminId = $this->getAdapter()->getConnection()->lastInsertId();

        $this->table('product')->insert([
            ['company_id' => $companyId, 'title' => 'produto da empresa 2', 'price' => 99.9, 'active' => 1, 'stock' => 5],
        ])->saveData();
        $productId = $this->getAdapter()->getConnection()->lastInsertId();

        echo "Empresa 2 criada. id da empresa: {$companyId}, id do admin: {$adminId}, id do produto: {$productId}\n";
    }
}