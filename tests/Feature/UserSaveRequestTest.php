<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;

use App\Http\Requests\UserSaveRequest;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserSaveRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function cc_emailsの桁数エラーとなること(): void
    {
        $data = [
            'cc_emails' => str_repeat('a', 2539) . '@example.com',
            'name' => '名前',
            'email' => 'email@example.com',
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertFalse($result);
        $expectedFailed = [
            'cc_emails' => ['Max' => [2550],],
        ];
        $this->assertEquals($expectedFailed, $validator->failed());
    }

    /**
     * @test
     * @return void
     */
    public function cc_emailsの最大email数エラーとなること(): void
    {
        $data = [
            'cc_emails' => '1@example.com,2@example.com,3@example.com,4@example.com,5@example.com,6@example.com,7@example.com,8@example.com,9@example.com,10@example.com,11@example.com',
            'name' => '名前',
            'email' => 'email@example.com',
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertFalse($result);
        $expectedFailed = [
            'cc_emails' => ['App\Rules\DelimitedMax' => [],],
        ];
        $this->assertEquals($expectedFailed, $validator->failed());
        $actualMessage = $validator->errors()->all()[0];
        $this->assertEquals('cc emails に指定できる email は最大 10 個です。', $actualMessage);
    }

    /**
     * @test
     * @return void
     */
    public function cc_emailsの中にメールアドレスとして不正なemailが存在するエラーとなること(): void
    {
        $data = [
            'cc_emails' => '1example.com,2@example.com,3atexample.com',
            'name' => '名前',
            'email' => 'email@example.com',
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertFalse($result);
        $expectedFailed = [
            'cc_emails' => ['Illuminate\Validation\ClosureValidationRule' => [],],
        ];
        //dd($validator->failed());
        $this->assertEquals($expectedFailed, $validator->failed());
        //dd($validator->errors());
        $actualMessage = $validator->errors()->all()[0];
        $this->assertEquals('cc_emails に指定した次のメールアドレスは不正です。: 1example.com, 3atexample.com', $actualMessage);
    }





    /**
     * @test
     * @return void
     */
    public function 必須エラーとなること(): void
    {
        $data = [
            'name' => null,
            'email' => null,
            'zip' => null,
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertFalse($result);
        $expectedFailed = [
            'name' => ['Required' => [],],
            'email' => ['Required' => [],],
        ];
        $this->assertEquals($expectedFailed, $validator->failed());
    }

    /**
     * @test
     * @return void
     */
    public function 桁数エラーとなること(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
            'email' => str_repeat('a', 244) . '@example.com',
            'zip' => str_repeat('a', 8),
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertFalse($result);
        $expectedFailed = [
            'name' => ['Max' => [255],],
            'email' => ['Max' => [255],],
            'zip' => ['Regex' => ['/^\d{7}$/'],],
        ];
        $this->assertEquals($expectedFailed, $validator->failed());
    }

    /**
     * @test
     * @return void
     */
    public function フォーマットエラーとなること(): void
    {
        $data = [
            'name' => '名前',
            'email' => 'aaa',
            'zip' => 'aaa',
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertFalse($result);
        $expectedFailed = [
            'email' => ['Email' => ['rfc', 'spoof',],],
            'zip' => ['Regex' => ['/^\d{7}$/'],],
        ];
        $this->assertEquals($expectedFailed, $validator->failed());
    }

    /**
     * @test
     * @return void
     */
    public function 正常系(): void
    {
        $data = [
            'name' => '名前',
            'email' => 'email@example.com',
            'zip' => '0123456',
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertTrue($result);
    }

    /**
     * @test
     * @return void
     */
    public function メールのユニークエラーとなること(): void
    {
        $user = factory(User::class)->create();

        $data = [
            'name' => '名前',
            'email' => $user->email,
            'zip' => '0123456',
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertFalse($result);
        $expectedFailed = [
            'email' => ['Unique' => ['users', 'NULL', 'NULL', 'id',],],
        ];
        $this->assertEquals($expectedFailed, $validator->failed());
    }

    /**
     * @test
     * @return void
     */
    public function メール未変更でも正常終了すること(): void
    {
        //DB::enableQueryLog();
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $data = [
            'name' => '名前',
            'email' => $user->email,
            'zip' => '0123456',
        ];
        $request = new UserSaveRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $this->assertTrue($result);
        //dd(DB::getQueryLog());
    }
}
