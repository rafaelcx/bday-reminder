<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

use App\Storage\Database\DatabaseResolver;
use App\Utils\Clock;
use PDO;

class BirthdayRepositoryInSqlite implements BirthdayRepository {

    public function create(string $user_uid, string $name, Clock $date): void {
        $sql = <<<SQL
            INSERT INTO birthdays (uid, user_uid, name, date, created_at)
            VALUES (:uid, :user_uid, :name, :date, :created_at);
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'uid' => uniqid(),
            'user_uid' => $user_uid,
            'name' => $name,
            'date' => $date->format('Y-m-d H:i:s'),
            'created_at' => Clock::now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return Birthday[]
     */
    public function findByUserUid(string $user_id): array {
        $sql = <<<SQL
            SELECT * FROM birthdays WHERE user_uid = :user_uid;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute(['user_uid' => $user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fn = fn(array $row) => new Birthday(
            uid: $row['uid'],
            user_uid: $row['user_uid'],
            name: $row['name'],
            date: Clock::at($row['date']),
            created_at: Clock::at($row['created_at']),
        );

        return array_map($fn, $rows ?: []);
    }

    public function findByUserUidInTheNextDays(string $user_uid, int $days): array {
        $all_birthdays = $this->findByUserUid($user_uid);

        $relevant_birthdays = array_filter($all_birthdays, function (Birthday $birthday) use ($days) {
            $today_date_string = Clock::now()->format('Y-m-d');
            $today = Clock::at($today_date_string);

            $birthday_this_year_date_string = $today->format('Y') . '-' . $birthday->date->format('m-d');
            $birthday_this_year = Clock::at($birthday_this_year_date_string);

            if ($today_date_string === $birthday_this_year_date_string) {
                return true;
            }

            if ($birthday_this_year->isBefore($today)) {
                $next_year = (int) $today->format('Y') + 1;
                $next_birthday_date_string = $next_year . '-' . $birthday->date->format('m-d');
                $next_birthday = Clock::at($next_birthday_date_string);
            } else {
                $next_birthday = $birthday_this_year;
            }

            $next_birthday_date_string = $next_birthday->format('Y-m-d');
            $cutoff_date_string = $today->plusDays($days)->format('Y-m-d');

            return $next_birthday_date_string > $today_date_string
                && $next_birthday_date_string <= $cutoff_date_string;
        });

        return array_values($relevant_birthdays);
    }

    public function update(string $birthday_uid, string $name, Clock $date): void {
        $sql = <<<SQL
            UPDATE birthdays
            SET name = :name, date = :date
            WHERE uid = :uid;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'uid' => $birthday_uid,
            'name' => $name,
            'date' => $date->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(string $birthday_uid): void {
        $sql = <<<SQL
            DELETE FROM birthdays WHERE uid = :uid;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute(['uid' => $birthday_uid]);
    }
}
