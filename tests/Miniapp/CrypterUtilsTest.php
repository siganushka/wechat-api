<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Miniapp;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wechat\Miniapp\CrypterUtils;

class CrypterUtilsTest extends TestCase
{
    #[DataProvider('getEncryptedDataProvider')]
    public function testDecrypt(string $sessionKey, string $encryptedData, string $iv): void
    {
        $data = CrypterUtils::decrypt($sessionKey, $encryptedData, $iv);
        static::assertArrayHasKey('openId', $data);
        static::assertArrayHasKey('nickName', $data);
        static::assertArrayHasKey('gender', $data);
        static::assertArrayHasKey('language', $data);
        static::assertArrayHasKey('city', $data);
        static::assertArrayHasKey('province', $data);
        static::assertArrayHasKey('country', $data);
        static::assertArrayHasKey('avatarUrl', $data);
        static::assertArrayHasKey('unionId', $data);
        static::assertArrayHasKey('watermark', $data);
    }

    public function testSessionKeyInvalidException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "session_key" is invalid');

        CrypterUtils::decrypt('foo', 'test', 'r7BXXKkLb8qrSNn05n0qiA==');
    }

    public function testIVInvalidException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "iv" is invalid');

        CrypterUtils::decrypt('tiihtNczf5v6AKRyjwEUhQ==', 'test', 'foo');
    }

    public function testUnableDecryptException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to decrypt value');

        CrypterUtils::decrypt('tiihtNczf5v6AKRyjwEUhQ==', 'test', 'r7BXXKkLb8qrSNn05n0qiA==');
    }

    public static function getEncryptedDataProvider(): iterable
    {
        yield [
            'tiihtNczf5v6AKRyjwEUhQ==',
            'CiyLU1Aw2KjvrjMdj8YKliAjtP4gsMZM
            QmRzooG2xrDcvSnxIMXFufNstNGTyaGS
            9uT5geRa0W4oTOb1WT7fJlAC+oNPdbB+
            3hVbJSRgv+4lGOETKUQz6OYStslQ142d
            NCuabNPGBzlooOmB231qMM85d2/fV6Ch
            evvXvQP8Hkue1poOFtnEtpyxVLW1zAo6
            /1Xx1COxFvrc2d7UL/lmHInNlxuacJXw
            u0fjpXfz/YqYzBIBzD6WUfTIF9GRHpOn
            /Hz7saL8xz+W//FRAUid1OksQaQx4CMs
            8LOddcQhULW4ucetDf96JcR3g0gfRK4P
            C7E/r7Z6xNrXd2UIeorGj5Ef7b1pJAYB
            6Y5anaHqZ9J6nKEBvB4DnNLIVWSgARns
            /8wR2SiRS7MNACwTyrGvt9ts8p12PKFd
            lqYTopNHR1Vf7XjfhQlVsAJdNiKdYmYV
            oKlaRv85IfVunYzO0IKXsyl7JCUjCpoG
            20f0a04COwfneQAGGwd5oa+T8yO5hzuy
            Db/XcxxmK01EpqOyuxINew==',
            'r7BXXKkLb8qrSNn05n0qiA==',
        ];
    }
}
