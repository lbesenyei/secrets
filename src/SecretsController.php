<?php

namespace Lbesenyei\Secrets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class SecretsController extends Controller
{
  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {
    try {
      // Post validation
      $this->validate($request, [
        'secret' => 'required|max:255',
        'expireAfter' => 'integer|min:0',
        'expireAfterViews' => 'required|integer|min:0'
      ]);
    } catch (ValidationException $e) {
      // Return validation fail message
      return \Response::json('Invalid input', 405);
    }

    // Get post data and current UNIX timestamp
    $content = $request->all();
    $current_time = Carbon::now()->getTimestamp();

    // Generate unique hash based on text, time and an incremental value
    // to avoid duplicates (same secret taxt posted at the same time)
    $i = 0;
    do {
      $hash = md5($content['secret'] . $current_time . $i);
      $results = DB::table('secrets')
                ->where('hash', $hash)
                ->get();

      $i++;
    } while($results->count());

    // Generate expiration date UNIX timestamp
    // based on expireAfter value (if given)

    if (isset($content['expireAfter']) && $content['expireAfter'] > 0) {
      $expiration_date =  $current_time + ($content['expireAfter'] * 60);
    } else {
      $expiration_date =  0;
    }

    // Build new database record
    $secret = [
      'hash' => $hash,
      'secretText' => $content['secret'],
      'createdAt' => $current_time,
      'expiresAt' => $expiration_date,
      'remainingViews' => $content['expireAfterViews']
    ];

    // Insert record to database
    DB::table('secrets')->insert($secret);

    // Return successful response
    return \Response::json($secret, 200);
  }

  /*
   * Display the specified resource.
   *
   * @param  int  $hash
   * @return Response
   */
  public function show($hash)
  {
    // Generate current UNIX timestamp
    $current_time = Carbon::now()->getTimestamp();

    // Search for secret with matching hash that has no longer expired
    // and has views remaining
    $results = DB::table('secrets')
                ->where('hash', $hash)
                ->where('remainingViews', '>', 0)
                ->where(function($query) use ($current_time) {
                  $query->where('expiresAt', '>', $current_time)
                        ->orWhere('expiresAt', '=', 0);
                })
                ->get();

    if ($results->count()) {
      // Fetch result and update remaining views counter
      $secret = $results->first();

      DB::table('secrets')
        ->where('hash', $hash)
        ->update(['remainingViews' => $secret->remainingViews - 1]);

      // Return response containing secret
      return \Response::json($secret, 200);
    } else {
      // Return secret not found error message
      return \Response::json('Secret not found', 404);
    }

  }
}
