<?php


namespace Tanmo\CtyunOOS;


use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Tanmo\OOS\OosClient;

class CtyunOosServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('oos', function ($app, $config) {
            $accessId  = $config['access_id'];
            $accessKey = $config['access_key'];

            $cdnDomain = empty($config['cdnDomain']) ? '' : $config['cdnDomain'];
            $bucket    = $config['bucket'];
            $ssl       = empty($config['ssl']) ? false : $config['ssl'];
            $isCname   = empty($config['isCName']) ? false : $config['isCName'];
            $options   = empty($config['options']) ? [] : $config['options'];
            $debug     = empty($config['debug']) ? false : $config['debug'];

            $endPoint  = $config['endpoint']; // 默认作为外部节点
            $epInternal= (empty($config['endpoint_internal']) ? $endPoint : $config['endpoint_internal']); // 内部节点

            if($debug) Log::debug('OOS config:', $config);

            $client  = new OosClient($accessId, $accessKey, $epInternal, false); // isCName 这里值为false的原因是，辣鸡电信云不支持CName绑定，只能自己搞转发
            $adapter = new OosAdapter($client, $bucket, $endPoint, $ssl, $cdnDomain, $isCname, $debug, null, $options);

            return new FilesystemAdapter(new Filesystem($adapter), $adapter, $config);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
    }
}
