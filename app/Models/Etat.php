<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use function PHPUnit\Framework\isEmpty;

/** Stores a part of the day.
 * @property int $ETA_id
 * @property string $ETA_libelle
 */
class Etat extends Model
{
    /** Just created, open to registratio/configuration */
    public static int $CREATED = 1;
    /** All palanquees are set and ok */
    public static int $PARAMETERIZED = 2;
    /** Dive is done, all parameters have been filled */
    public static int $VALIDATED = 3;
    /** Dive is at least one-year-old */
    public static int $OLD = 4;
    /** Dive has been canceled */
    public static int $CANCELLED = 5;

    /** @var Etat[] $states */
    private static array $states = [];

    public static function find(int $id) : Etat {
        if (isEmpty(Etat::$states))
            foreach (Etat::all() as $state)
                Etat::$states[] = $state;
        return Etat::$states[$id - 1];
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_ETATS';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ETA_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function toArray(): array
    {
        return [
            "id" => $this->ETA_id,
            "libelle" => $this->ETA_libelle
        ];
    }

    /** Gets the unique id for selecting in views. */
    public function getId() : int {
        return $this->ETA_id;
    }

    /** Gets the text to display when selecting in views. */
    public function getText() : string {
        return $this->ETA_libelle;
    }

}
