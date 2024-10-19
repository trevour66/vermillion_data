<?php

namespace App\Http\Controllers;

use App\Models\Company_Database;
use Error;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $website_data = Company_Database::latest()->paginate(20);

        return Inertia::render('Dashboard', [
            "data" => $website_data
        ]);
    }

    public function index_api()
    {
        $website_data = Company_Database::latest()->paginate(30);
        return Inertia::render('Dashboard');
    }

    public function add_websites_api(Request $request)
    {
        try {
            $validated = $request->validate([
                'websites' => 'required|array'
            ]);

            $properly_formatted_domains = [];
            $improperly_formatted_domains = [];

            foreach ($validated['websites'] as $key => $value) {
                logger($value);

                $domain = $this->validateDomain($value);

                if ($domain) {
                    $formatted_website_name = "www." . $domain;

                    logger('accepted');
                    Company_Database::updateOrCreate(
                        ['website' => $formatted_website_name],
                        ['website' => $formatted_website_name]
                    );

                    array_push($properly_formatted_domains, [$formatted_website_name]);
                } else {
                    logger('not accepted');
                    array_push($improperly_formatted_domains, $value);
                }
            }

            $this->sendToSheet($properly_formatted_domains);

            return response()->json([
                'status' => 'success',
                'improperly_formatted_domains' => $improperly_formatted_domains
            ]);
        } catch (\Throwable $th) {
            logger($th->getMessage());
            return response()->json([
                'status' => 'error',
            ]);
        }
    }

    private function validateDomain($domain)
    {
        $domain = $this->removePrefix_HTTP_HTTPS_WWW($domain);
        // logger($domain);
        $domain = $this->removeQueryParams($domain);
        // logger($domain);

        if (!$domain) {
            return false;
        }

        $isDomainValid = (bool) preg_match('/^(?!https?:\/\/)(?!www\.)(?!\-)(?:[a-zA-Z0-9\-]{1,63}\.)+(?:[a-zA-Z]{2,63})$/', $domain);

        // logger($isDomainValid);

        if ($isDomainValid) {
            return $domain;
        } else {
            return false;
        }
    }

    private function removePrefix_HTTP_HTTPS_WWW($website)
    {
        $website = strtolower($website);

        // logger('here');

        if (strpos($website, 'www.') === 0) {
            $website = substr($website, strlen('www.'));
            // logger($website);
            return $this->removePrefix_HTTP_HTTPS_WWW($website);
        }

        if (strpos($website, 'http://') === 0) {
            $website = substr($website, strlen('http://'));
            // logger($website . 'http://');
            return $this->removePrefix_HTTP_HTTPS_WWW($website);
        }

        if (strpos($website, 'https://') === 0) {
            $website = substr($website, strlen('https://'));
            // logger($website . 'https://');
            return $this->removePrefix_HTTP_HTTPS_WWW($website);
        }

        return $website;
    }

    private function removeQueryParams($website)
    {
        $website = strtolower($website);
        $website = explode('/', $website);

        $website = $website[0] ?? '';

        if ($website === '') return false;

        $website = explode('?', $website);

        // logger($website);

        return $website[0] ?? false;
    }

    private function sendToSheet($data)
    {
        try {
            $data = (array) $data;

            if (count($data) == 0) return;

            $GOOGLE_APP_SCRIPT_URL =  env('GOOGLE_APP_SCRIPT_URL', null);

            if ($GOOGLE_APP_SCRIPT_URL == null) {
                throw new Error('GOOGLE_APP_SCRIPT_URL not in .env');
            }

            $request = Http::post($GOOGLE_APP_SCRIPT_URL, [
                "websites" => $data
            ]);

            logger($request->body());
        } catch (\Throwable $th) {
            logger($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company_Database $website_id)
    {
        $website_id->delete();
        return back();
    }
}
