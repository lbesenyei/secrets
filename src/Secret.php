<?php

namespace Lbesenyei\Secrets;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Secret extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'secrets';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'hash';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';


    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var array
     */
    public $fillable = ['secretText','createdAt','expiresAt','remainingViews'];

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    private $salt = 'scrtslt';

    public function __construct(array $attributes = []) {
      if (!$this->exists && !empty($attributes)) {
        $current_time = Carbon::now()->getTimestamp();

        if (isset($attributes['expireAfter']) && $attributes['expireAfter'] > 0) {
          $expiration_date =  $current_time + ($attributes['expireAfter'] * 60);
        } else {
          $expiration_date =  0;
        }

        $data = [
          'secretText' => $attributes['secret'],
          'createdAt' => $current_time,
          'expiresAt' => $expiration_date,
          'remainingViews' => $attributes['expireAfterViews'],
        ];

        parent::__construct($data);

        $this->generateHash();
      } else {
        parent::__construct($attributes);
      }
    }

    public function generateHash() {
      // Generate unique hash based on text, time and an incremental value
      // to avoid duplicates (same secret taxt posted at the same time)
      $i = 0;
      do {
        $hash = crypt($this->secret . $i, $this->salt);
        $results = Secret::where('hash', $hash)
                  ->get();

        $i++;
      } while($results->count());

      $this->hash = $hash;
    }

    public static function byHash($hash){
      $current_time = Carbon::now()->getTimestamp();

      // Search for secret with matching hash that has no longer expired
      // and has views remaining
      $results = Secret::where('hash', $hash)
                ->where('remainingViews', '>', 0)
                ->where(function($query) use ($current_time) {
                  $query->where('expiresAt', '>', $current_time)
                        ->orWhere('expiresAt', '=', 0);
                })
                ->get();

      if ($results->count()) {
        return $results->first();
      } else {
        return false;
      }
    }
}
