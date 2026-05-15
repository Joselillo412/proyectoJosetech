namespace App\Services\Api;

use Illuminate\Support\Facades\Http;

abstract class BaseApiService {
    protected $baseUrl;
    protected $apiKey;

    public function __construct() {
        $this->baseUrl = config("services.{$this->getServiceName()}.url");
        $this->apiKey = config("services.{$this->getServiceName()}.key");
    }

    abstract protected function getServiceName();

    protected function request() {
        return Http::withoutVerifying()->withToken($this->apiKey);
    }
}