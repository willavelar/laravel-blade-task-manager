<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $categories = $user->categories()->pluck('id', 'name');

        $tasks = [
            [
                'title' => 'Revisar relatório mensal',
                'description' => 'Verificar números de março e preparar apresentação para o gerente.',
                'category_id' => $categories['Trabalho'],
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Responder e-mails pendentes',
                'description' => null,
                'category_id' => $categories['Trabalho'],
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDay()->toDateString(),
            ],
            [
                'title' => 'Estudar para certificação Laravel',
                'description' => 'Cobrir os capítulos de Queues e Events.',
                'category_id' => $categories['Estudo'],
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'Ler livro Clean Code',
                'description' => null,
                'category_id' => $categories['Estudo'],
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => null,
            ],
            [
                'title' => 'Comprar mantimentos',
                'description' => 'Arroz, feijão, azeite, ovos.',
                'category_id' => $categories['Pessoal'],
                'priority' => 'low',
                'status' => 'completed',
                'due_date' => now()->subDay()->toDateString(),
            ],
            [
                'title' => 'Academia — treino de peito',
                'description' => null,
                'category_id' => $categories['Saúde'],
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->toDateString(),
            ],
            [
                'title' => 'Pagar fatura do cartão',
                'description' => null,
                'category_id' => $categories['Finanças'],
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'title' => 'Configurar backup automático do PC',
                'description' => null,
                'category_id' => $categories['Pessoal'],
                'priority' => 'low',
                'status' => 'completed',
                'due_date' => null,
            ],
        ];

        foreach ($tasks as $task) {
            $user->tasks()->create($task);
        }
    }
}
