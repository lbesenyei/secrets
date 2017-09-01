<?php

namespace Lbesenyei\Secrets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Orchestra\Parser\Xml\Facade as XmlParser;

class SecretsController extends Controller
{

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {

    switch ($request->header('Content-Type')) {
      case 'application/xml':
        // Parse XML Output
        $xml = XmlParser::extract($request->getContent());
        $post_data = $xml->parse([
          'secret' => ['uses' => 'secret'],
          'expireAfter' => ['uses' => 'expireAfter'],
          'expireAfterViews' => ['uses' => 'expireAfterViews']
        ]);

        break;

      case 'application/json':
        $post_data = $request->all();
        break;

      default:
        return response('Invalid input', 405);
        break;
    }

    // Define Validation for posted data
    $validator = \Validator::make($post_data, [
      'secret' => 'required|max:255',
      'expireAfter' => 'nullable|integer|min:0',
      'expireAfterViews' => 'required|integer|min:0'
    ]);

    if ($validator->fails()) {
      return response('Invalid input', 405);
    }

    // Create new Secret
    $secret = new Secret($post_data);

    $secret->save();

    // Return output depending on Content-Type
    switch ($request->header('Content-Type')) {
      case 'application/xml':
        return view('secrets::secret', ['secret' => $secret]);
        break;

      default:
        return \Response::json($secret, 200);
        break;
    }
  }

  /*
   * Display the specified resource.
   *
   * @param  int  $hash
   * @return Response
   */
  public function show($hash, Request $request)
  {
    $secret = Secret::byHash($hash);

    if ($secret) {
      // Fetch result and update remaining views counter
      $secret->remainingViews = $secret->remainingViews - 1;

      $secret->save();

      // Return output depending on Content-Type
      switch ($request->header('Content-Type')) {
        case 'application/xml':
          return view('secrets::secret', ['secret' => $secret]);
          break;

        default:
          return \Response::json($secret, 200);
          break;
      }
      // Return response containing secret
    } else {
      // Return secret not found error message
      return response('Secret not found', 404);
    }
  }
}
