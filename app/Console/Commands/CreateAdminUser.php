<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    // C'est le nom de la commande que tu taperasi dans le terminal
    protected $signature = 'make:admin';

    protected $description = 'Créer un compte administrateur initial pour le Marketplace';

    public function handle()
    {
        $email = 'admin@epf.sn'; // Tu pourras modifier l'email ici si tu veux
        
        // On vérifie si l'admin existe déjà pour éviter les doublons
        if (User::where('email', $email)->exists()) {
            $this->error('Un utilisateur avec cet email existe déjà !');
            return Command::FAILURE;
        }

        // Création de l'administrateur
        $admin = User::create([
            'name' => 'Administrateur EPF',
            'email' => $email,
            'password' => Hash::make('AdminEpf2026!'), // Change ce mot de passe par sécurité
            'role' => 'admin', // Assure-toi que ta table users possède bien une colonne role (ou un attribut similaire)
            'bio' => 'Compte administrateur principal du Marketplace.',
        ]);

        $this->info('Compte administrateur créé avec succès !');
        $this->info('Email : ' . $email);
        
        return Command::SUCCESS;
    }
}