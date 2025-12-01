-- PostgreSQL schema for THODZ (Koyeb deployment)
-- Run this on your Koyeb PostgreSQL database

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS likes CASCADE;
DROP TABLE IF EXISTS posts CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users table
CREATE TABLE users (
    uid SERIAL PRIMARY KEY,
    fname VARCHAR(20) NOT NULL,
    lname VARCHAR(20) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    email VARCHAR(256) NOT NULL UNIQUE,
    password VARCHAR(256) NOT NULL,
    profileimg VARCHAR(256) NOT NULL DEFAULT '',
    isemailconfirmed SMALLINT NOT NULL DEFAULT 0,
    token VARCHAR(10) NOT NULL DEFAULT '',
    likes INTEGER NOT NULL DEFAULT 0,
    about TEXT NOT NULL DEFAULT '',
    status VARCHAR(8) NOT NULL DEFAULT 'offline',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE posts (
    pid SERIAL PRIMARY KEY,
    post TEXT NOT NULL DEFAULT '',
    postimg VARCHAR(256) NOT NULL DEFAULT '',
    has_image SMALLINT NOT NULL DEFAULT 0,
    is_profileimg SMALLINT NOT NULL DEFAULT 0,
    parent INTEGER NOT NULL DEFAULT 0,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    owner INTEGER NOT NULL,
    likes INTEGER NOT NULL DEFAULT 0,
    comments INTEGER NOT NULL DEFAULT 0
);

-- Likes table
CREATE TABLE likes (
    lid SERIAL PRIMARY KEY,
    type VARCHAR(10) NOT NULL,
    likes TEXT NOT NULL,
    contentid INTEGER NOT NULL
);

-- Messages table
CREATE TABLE messages (
    mid SERIAL PRIMARY KEY,
    incoming_msg_id INTEGER NOT NULL,
    outgoing_msg_id INTEGER NOT NULL,
    msg TEXT NOT NULL DEFAULT '',
    image VARCHAR(256) DEFAULT NULL,
    audio VARCHAR(256) DEFAULT NULL,
    is_read SMALLINT NOT NULL DEFAULT 0,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_posts_owner ON posts(owner);
CREATE INDEX idx_posts_parent ON posts(parent);
CREATE INDEX idx_messages_incoming ON messages(incoming_msg_id);
CREATE INDEX idx_messages_outgoing ON messages(outgoing_msg_id);
CREATE INDEX idx_likes_contentid ON likes(contentid);
CREATE INDEX idx_users_email ON users(email);

-- Insert demo users (password is 'THODZ' + md5('1234') = 'THODZ81dc9bdb52d04dc20036dbd8313ed055')
INSERT INTO users (fname, lname, gender, email, password, profileimg, isemailconfirmed, token, likes, about, status)
VALUES 
    ('Demo', 'UserOne', 'male', 'user1@example.com', 'THODZ81dc9bdb52d04dc20036dbd8313ed055', '', 1, '', 0, '', 'offline'),
    ('Demo', 'UserTwo', 'male', 'user2@example.com', 'THODZ81dc9bdb52d04dc20036dbd8313ed055', '', 1, '', 0, '', 'offline'),
    ('Demo', 'UserThree', 'male', 'user3@example.com', 'THODZ81dc9bdb52d04dc20036dbd8313ed055', '', 1, '', 0, '', 'offline');
