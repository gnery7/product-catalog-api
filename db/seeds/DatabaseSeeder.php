<?php

use Phinx\Seed\AbstractSeed;

class DatabaseSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('company')->insert([
            ['id' => 1, 'name' => 'XPTO Ltda.', 'active' => 1],
        ])->saveData();

        $this->table('admin_user')->insert([
            ['id' => 1, 'company_id' => 1, 'email' => 'rivers.cuomo@xpto.com', 'name' => 'rivers'],
            ['id' => 2, 'company_id' => 1, 'email' => 'kim.deal@xpto.com', 'name' => 'kim'],
            ['id' => 3, 'company_id' => 1, 'email' => 'corin.tucker@xpto.com', 'name' => 'corin'],
            ['id' => 4, 'company_id' => 1, 'email' => 'jeff.magnum@xpto.com', 'name' => 'jeff'],
        ])->saveData();

        $this->table('category')->insert([
            ['id' => 1, 'company_id' => null, 'title' => 'clothing', 'active' => 1],
            ['id' => 2, 'company_id' => null, 'title' => 'phone', 'active' => 1],
            ['id' => 3, 'company_id' => null, 'title' => 'computer', 'active' => 1],
            ['id' => 4, 'company_id' => 1, 'title' => 'furniture', 'active' => 1],
            ['id' => 5, 'company_id' => 1, 'title' => 'food', 'active' => 1],
            ['id' => 6, 'company_id' => null, 'title' => 'house', 'active' => 1],
        ])->saveData();

        $this->table('product')->insert([
            ['id' => 1, 'company_id' => 1, 'title' => 'white shirt', 'price' => 70.5, 'active' => 1, 'created_at' => '2023-12-20 21:05:48', 'stock' => 0],
            ['id' => 2, 'company_id' => 1, 'title' => 'blue trouser', 'price' => 68.5, 'active' => 1, 'created_at' => '2023-12-20 21:05:48', 'stock' => 0],
            ['id' => 3, 'company_id' => 1, 'title' => 'brown hat', 'price' => 20.7, 'active' => 1, 'created_at' => '2023-12-20 21:05:48', 'stock' => 0],
            ['id' => 4, 'company_id' => 1, 'title' => 'iphone 8', 'price' => 18, 'active' => 1, 'created_at' => '2023-12-20 21:05:48', 'stock' => 0],
            ['id' => 5, 'company_id' => 1, 'title' => 'iphone 10', 'price' => 2790.75, 'active' => 1, 'created_at' => '2023-12-20 21:05:48', 'stock' => 0],
            ['id' => 6, 'company_id' => 1, 'title' => 'dell vostro', 'price' => 2450.5, 'active' => 1, 'created_at' => '2023-12-20 21:05:48', 'stock' => 0],
            ['id' => 7, 'company_id' => 1, 'title' => 'acer aspire', 'price' => 1804.05, 'active' => 1, 'created_at' => '2023-12-20 21:05:48', 'stock' => 0],
            ['id' => 8, 'company_id' => 1, 'title' => 'pink sofa', 'price' => 1396.5, 'active' => 1, 'created_at' => '2023-12-20 21:08:27', 'stock' => 0],
            ['id' => 9, 'company_id' => 1, 'title' => 'small wardrobe', 'price' => 680.25, 'active' => 1, 'created_at' => '2023-12-20 21:08:27', 'stock' => 0],
            ['id' => 10, 'company_id' => 1, 'title' => 'king size bed', 'price' => 4520.83, 'active' => 1, 'created_at' => '2023-12-20 21:08:27', 'stock' => 0],
            ['id' => 11, 'company_id' => 1, 'title' => 'big red couch', 'price' => 2520.83, 'active' => 0, 'created_at' => '2023-12-20 21:08:27', 'stock' => 0],
            ['id' => 12, 'company_id' => 1, 'title' => 'chocolate snack', 'price' => 20, 'active' => 1, 'created_at' => '2023-12-20 21:12:22', 'stock' => 0],
            ['id' => 13, 'company_id' => 1, 'title' => 'honey flavoured cookie', 'price' => 10.21, 'active' => 0, 'created_at' => '2023-12-20 21:12:22', 'stock' => 0],
            ['id' => 14, 'company_id' => 1, 'title' => 'strawberry bubblegum', 'price' => 4520.83, 'active' => 1, 'created_at' => '2023-12-20 21:12:22', 'stock' => 0],
            ['id' => 15, 'company_id' => 1, 'title' => 'muffin', 'price' => 14.24, 'active' => 1, 'created_at' => '2023-12-20 21:12:22', 'stock' => 0],
            ['id' => 16, 'company_id' => 1, 'title' => 'coffee candy', 'price' => 1.8, 'active' => 0, 'created_at' => '2023-12-20 21:12:22', 'stock' => 0],
            ['id' => 17, 'company_id' => 1, 'title' => 'air conditioning', 'price' => 2700, 'active' => 1, 'created_at' => '2023-12-20 21:19:58', 'stock' => 0],
            ['id' => 18, 'company_id' => 1, 'title' => 'refrigerator', 'price' => 680.5, 'active' => 1, 'created_at' => '2023-12-21 15:31:45', 'stock' => 0],
        ])->saveData();

        $this->table('product_category')->insert([
            ['id' => 1, 'cat_id' => 1, 'product_id' => 1],
            ['id' => 2, 'cat_id' => 1, 'product_id' => 2],
            ['id' => 3, 'cat_id' => 1, 'product_id' => 3],
            ['id' => 4, 'cat_id' => 2, 'product_id' => 4],
            ['id' => 5, 'cat_id' => 2, 'product_id' => 5],
            ['id' => 6, 'cat_id' => 3, 'product_id' => 6],
            ['id' => 7, 'cat_id' => 3, 'product_id' => 7],
            ['id' => 8, 'cat_id' => 4, 'product_id' => 8],
            ['id' => 9, 'cat_id' => 6, 'product_id' => 8],
            ['id' => 10, 'cat_id' => 4, 'product_id' => 9],
            ['id' => 11, 'cat_id' => 6, 'product_id' => 9],
            ['id' => 12, 'cat_id' => 4, 'product_id' => 10],
            ['id' => 13, 'cat_id' => 6, 'product_id' => 10],
            ['id' => 14, 'cat_id' => 4, 'product_id' => 11],
            ['id' => 15, 'cat_id' => 6, 'product_id' => 11],
            ['id' => 16, 'cat_id' => 5, 'product_id' => 12],
            ['id' => 17, 'cat_id' => 5, 'product_id' => 13],
            ['id' => 18, 'cat_id' => 5, 'product_id' => 14],
            ['id' => 19, 'cat_id' => 5, 'product_id' => 15],
            ['id' => 20, 'cat_id' => 5, 'product_id' => 16],
            ['id' => 21, 'cat_id' => 6, 'product_id' => 17],
            ['id' => 22, 'cat_id' => 6, 'product_id' => 18],
        ])->saveData();

        $this->table('product_log')->insert([
            ['id' => 1, 'product_id' => 1, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 2, 'product_id' => 1, 'admin_user_id' => 2, 'action' => 'update', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 3, 'product_id' => 1, 'admin_user_id' => 3, 'action' => 'update', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 4, 'product_id' => 11, 'admin_user_id' => 3, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 5, 'product_id' => 11, 'admin_user_id' => 1, 'action' => 'update', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 6, 'product_id' => 11, 'admin_user_id' => 4, 'action' => 'delete', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 7, 'product_id' => 2, 'admin_user_id' => 2, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 8, 'product_id' => 3, 'admin_user_id' => 3, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 9, 'product_id' => 4, 'admin_user_id' => 4, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 10, 'product_id' => 2, 'admin_user_id' => 5, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 11, 'product_id' => 4, 'admin_user_id' => 6, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 12, 'product_id' => 7, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 13, 'product_id' => 8, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 14, 'product_id' => 9, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 15, 'product_id' => 10, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 16, 'product_id' => 11, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 17, 'product_id' => 12, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 18, 'product_id' => 13, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 19, 'product_id' => 14, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 20, 'product_id' => 15, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 21, 'product_id' => 16, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 22, 'product_id' => 17, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 21:32:22'],
            ['id' => 23, 'product_id' => 18, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-20 23:49:54'],
            ['id' => 24, 'product_id' => 18, 'admin_user_id' => 1, 'action' => 'update', 'timestamp' => '2023-12-20 23:52:58'],
            ['id' => 25, 'product_id' => 18, 'admin_user_id' => 1, 'action' => 'update', 'timestamp' => '2023-12-20 23:53:10'],
            ['id' => 26, 'product_id' => 18, 'admin_user_id' => 1, 'action' => 'update', 'timestamp' => '2023-12-21 00:03:55'],
            ['id' => 27, 'product_id' => 18, 'admin_user_id' => 1, 'action' => 'delete', 'timestamp' => '2023-12-21 00:04:35'],
            ['id' => 28, 'product_id' => 18, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2023-12-21 15:31:45'],
            ['id' => 29, 'product_id' => 4, 'admin_user_id' => 1, 'action' => 'update', 'timestamp' => '2023-12-22 18:08:12'],
            ['id' => 30, 'product_id' => 4, 'admin_user_id' => 3, 'action' => 'update', 'timestamp' => '2023-12-22 18:12:10'],
            ['id' => 31, 'product_id' => 19, 'admin_user_id' => 1, 'action' => 'create', 'timestamp' => '2024-01-04 02:44:37'],
            ['id' => 32, 'product_id' => 19, 'admin_user_id' => 1, 'action' => 'update', 'timestamp' => '2024-01-04 02:44:53'],
            ['id' => 33, 'product_id' => 19, 'admin_user_id' => 1, 'action' => 'delete', 'timestamp' => '2024-01-04 02:45:00'],
            ['id' => 34, 'product_id' => 19, 'admin_user_id' => 1, 'action' => 'delete', 'timestamp' => '2024-01-05 15:46:42'],
        ])->saveData();
    }
}
