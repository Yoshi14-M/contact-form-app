<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ページネーションされて表示される(): void
    {
        //Arrange
        $user = User::factory()->create();
        Contact::factory()->count(10)->create();
        //Act
        $response = $this->actingAs($user)->get(route('admin.index'));
        //Assert
        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts instanceof \Illuminate\Pagination\LengthAwarePaginator
                && $contacts->count() === 7   // 1ページ目の表示件数が7件であること
                && $contacts->total() === 10; // 総データ数が10件として認識されていること
        });
    }
    /** @test */
    public function 詳細画面が表示できる(): void
    {
        //Arrange
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        //Act
        $response = $this->actingAs($user)->get(route('admin.contacts.show', $contact));
        //Assert
        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertViewHas('contact', function ($viewContact) use ($contact) {
            return $viewContact->id === $contact->id;
        });
    }
    /** @test */
    public function 問い合わせが削除できる(): void
    {
        //Arrange
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        //Act
        $response = $this->actingAs($user)->delete(route('admin.contacts.destroy', $contact));
        //Assert
        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
    /** @test */
    public function 未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        //Act
        $response = $this->get(route('admin.index'));
        //Assert
        $response->assertRedirect(route('login'));
    }
}
