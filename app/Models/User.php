<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modele utilisateur principal de la plateforme.
 *
 * Role metier:
 * - Porte l'identite de connexion (email/password).
 * - Definit le role applicatif (client, prestataire, admin).
 * - Sert de point d'entree pour toutes les relations metier de l'utilisateur.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Champs autorises en assignation de masse.
     *
     * Explication des colonnes:
     * - name: nom affiche de l'utilisateur.
     * - email: identifiant unique de connexion.
     * - password: mot de passe hashé (voir cast password).
     * - role: type d'acteur (client, prestataire, admin).
     * - status: etat du compte (active, suspendu, etc.).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * Champs masques dans les reponses JSON.
     * Evite d'exposer des informations sensibles.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts automatiques Eloquent.
     *
     * - email_verified_at: manipule comme objet date/heure.
     * - password: hash automatique lors de l'affectation.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Profil detaille (1 utilisateur -> 1 profil).
     * Contient prenom, nom, telephone, avatar.
     */
    public function profil(): HasOne
    {
        return $this->hasOne(Profil::class);
    }

    /**
     * Historique d'adhesion aux prestataires via la table pivot metier.
     */
    public function prestataireMembres(): HasMany
    {
        return $this->hasMany(PrestataireMembre::class);
    }

    /**
     * Prestataires rattaches a cet utilisateur (relation many-to-many).
     * Utilise pour les comptes prestataire/membres d'equipe.
     */
    public function prestataires(): BelongsToMany
    {
        return $this->belongsToMany(Prestataire::class, 'prestataire_membres')
            ->withPivot(['role_membre', 'rejoint_le'])
            ->withTimestamps();
    }

    /**
     * Paniers crees par l'utilisateur (actif, converti, abandonne...).
     */
    public function paniers(): HasMany
    {
        return $this->hasMany(Panier::class);
    }

    /**
     * Reservations effectuees par l'utilisateur client.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Avis publies par l'utilisateur sur les activites reservees.
     */
    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    /**
     * Favoris sauvegardes par l'utilisateur.
     */
    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class);
    }

    /**
     * Journal des notifications envoyees a cet utilisateur.
     */
    public function journalNotifications(): HasMany
    {
        return $this->hasMany(JournalNotification::class);
    }

    /**
     * Evenements de tracking/statistiques associes a cet utilisateur.
     */
    public function evenementsStatistiques(): HasMany
    {
        return $this->hasMany(EvenementStatistique::class);
    }

    /**
     * Litiges ouverts en tant que client.
     */
    public function litigesOuverts(): HasMany
    {
        return $this->hasMany(Litige::class, 'client_id');
    }

    /**
     * Litiges assignes a cet utilisateur en tant qu'administrateur.
     */
    public function litigesAssignes(): HasMany
    {
        return $this->hasMany(Litige::class, 'admin_id');
    }

    /**
     * Signalements d'avis emis par cet utilisateur.
     */
    public function signalementsAvis(): HasMany
    {
        return $this->hasMany(SignalementAvis::class);
    }

    /**
     * Demandes de remboursement initiees par cet utilisateur.
     */
    public function remboursementsDemandes(): HasMany
    {
        return $this->hasMany(Remboursement::class, 'demandeur_id');
    }
}
