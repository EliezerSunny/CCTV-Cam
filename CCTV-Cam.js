#!/usr/bin/env node
// github.com/EliezerSunny/CCTV-Cam

const axios = require('axios');
const fs = require('fs');
const cheerio = require('cheerio');

const url = "http://www.insecam.org/en/jsoncountries/";

const headers = {
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
    "Cache-Control": "max-age=0",
    "Connection": "keep-alive",
    "Host": "www.insecam.org",
    "Upgrade-Insecure-Requests": "1",
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36"
};

async function fetchData(url) {
    try {
        const response = await axios.get(url, { headers });
        return response.data;
    } catch (error) {
        throw Error(`Error fetching data: ${error.message}`);
    }
}

async function fetchCountryInfo(countryCode) {
    try {
        const response = await axios.get(`http://www.insecam.org/en/bycountry/${countryCode}`, { headers });
        const html = response.data;
        const $ = cheerio.load(html);
        const lastPageMatch = $('script:contains("pagenavigator(\'?page=\'")').html();
        const lastPage = parseInt(lastPageMatch.match(/pagenavigator\('\?page='\, (\d+)/)[1]);
        return lastPage;
    } catch (error) {
        throw Error(`Error fetching country info: ${error.message}`);
    }
}

async function main() {
    try {
        const data = await fetchData(url);
        const countries = data.countries;

        console.log(`
        \x1b[1;31m\x1b[1;37m 
        _____________________________    __   _________
__  ____/__  ____/___  __/__ |  / /   __  ____/______ ________ ___
_  /     _  /     __  /   __ | / /    _  /     _  __ \`/__  __ \`__ \\
/ /___   / /___   _  /    __ |/ /     / /___   / /_/ / _  / / / / /
\\____/   \\____/   /_/     _____/      \\____/   \\__,_/  /_/ /_/ /_/

        \x1b[1;31m                                                                        EliezerSunny \x1b[1;31m\x1b[1;37m
        `);

        for (const [key, value] of Object.entries(countries)) {
            console.log(`Code : (${key}) - ${value.country} / (${value.count})  \n`);
        }

        const countryCode = await new Promise((resolve) => {
            const readline = require('readline').createInterface({
                input: process.stdin,
                output: process.stdout
            });
            readline.question("Code(##) : ", (countryCode) => {
                resolve(countryCode);
                readline.close();
            });
        });

        const lastPage = await fetchCountryInfo(countryCode);

        const filename = `${countryCode}.txt`;
        const fileStream = fs.createWriteStream(filename);

        for (let page = 0; page < lastPage; page++) {
            const response = await axios.get(`http://www.insecam.org/en/bycountry/${countryCode}/?page=${page}`, { headers });
            const html = response.data;
            const $ = cheerio.load(html);
            const find_ip = $('a[href^="http://"]').toArray().map(element => $(element).attr('href'));
            
            find_ip.forEach(ip => {
                console.log(`\n\x1b[1;31m ${ip}`);
                fileStream.write(`${ip}\n`);
            });
        }

        console.log("\x1b[1;37m");
        console.log(`\x1b[37mSave File : ${filename}`);

    } catch (error) {
        console.error(`An error occurred: ${error.message}`);
    } finally {
        process.exit();
    }
}

main();
