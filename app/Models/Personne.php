<?php

namespace App\Models;

use Database\Factories\PersonneFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Sanctum\HasApiTokens;

/** Stores a person.
 * @property int $PER_id
 * @property string $PER_nom
 * @property string $PER_prenom
 * @property string $PER_pass
 * @property string $PER_email
 * @property string $PER_remember_token
 * @property boolean $PER_active
 * @property Autorisations $autorisations
 * @property Adherent $adherent
 * @method static Personne find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 * @method static Builder whereHas($col, $op = null)
 * @method static PersonneFactory factory(...$parameters)
 */
class Personne extends Model implements Authenticatable
{
    use HasApiTokens, HasFactory, Authorizable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_PERSONNES';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'PER_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function toArray(): array
    {
        $res = [
            "id"=>$this->PER_id,
            "nom"=>$this->PER_nom,
            "prenom"=>$this->PER_prenom,
            "email"=>$this->PER_email,
            'actif' =>$this->PER_active
        ];
        if ($this->relationLoaded("autorisations") && isset($this->autorisations))
            return array_merge($res, [
                "directeur_de_section" => $this->autorisations->AUT_directeur_section,
                "securite_de_surface" => $this->autorisations->AUT_securite_surface,
                "pilote" => $this->autorisations->AUT_pilote,
                "secretaire" => $this->autorisations->AUT_secretaire
            ]);
        return $res;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'PER_active' => 'boolean',
        'AUT_directeur_section' => 'boolean',
        'AUT_securite_surface' => 'boolean',
        'AUT_pilote' => 'boolean',
        'AUT_secretaire' => 'boolean',
    ];

    // Relationships

    /** Get the authorizations of this person. */
    public function autorisations(): HasOne
    {
        return $this->hasOne(Autorisations::class, "AUT_personne", "PER_id");
    }

    /** Get the adherent of this person. */
    public function adherent(): HasOne
    {
        return $this->hasOne(Adherent::class, "ADH_id", "PER_id");
    }

    // Part for implementing interface Authenticatable

    /** Get hashed password from database */
    public function getAuthPassword(): string
    {
        return $this->PER_pass;
    }

    /** Get the name of the unique identifier for the user. */
    public function getAuthIdentifierName(): string
    {
        return "PER_id";
    }

    /** Get the unique identifier for the user. */
    public function getAuthIdentifier(): int
    {
        return $this->PER_id;
    }

    /** Get the token value for the "remember me" session. */
    public function getRememberToken(): ?string
    {
        return $this->PER_remember_token;
    }

    /** Set the token value for the "remember me" session. */
    public function setRememberToken($value)
    {
        $this->PER_remember_token = $value;
        $this->save();
    }

    /** Get the column name for the "remember me" token. */
    public function getRememberTokenName(): string
    {
        return "PER_remember_token";
    }

    /** Check if the person is a director of a section. */
    public function isDirector(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_directeur_section;
    }

    public function isLastDirector(): bool {
        return Autorisations::where('AUT_directeur_section', 1)->whereHas('personne', 
            function (Builder $query) {
                $query->where('PER_active', true);
            }
        )->count() === 1 && $this->isDirector();
    }

    /** Check if the person is a secretary. */
    public function isSecretary(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_secretaire;
    }

    /** Check if the person is a surface security. */
    public function isSurfaceSecurity(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_securite_surface;
    }

    /** Check if the person is a pilot. */
    public function isPilot(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_pilote;
    }

    /** Check if the person is an adherent. */
    public function isAdherent(): bool
    {
        return $this->adherent()->exists();
    }

    /** Get the ID of the person. */
    public function getId(): int {
        return $this->PER_id;
    }

    /** Get the full name of the person. */
    public function getText(): string {
        return "$this->PER_nom $this->PER_prenom";
    }

}
