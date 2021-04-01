const random = (min, max) => Math.floor(Math.random() * (max - min)) + min;

const find = (totalMin = 2, users = []) => {
  cards = [].slice.call(document.getElementsByClassName("user-list-item"));

  cards = cards
    .map((i) => {
      const as = [].slice.call(i.getElementsByTagName("a"));
      if (as.length >= 2) {
        const name = as[1].innerText.trim();
        const firstName = name.split(" ")[0].trim();
        const nick = as[1].href.trim();
        const emailLink = as.find((i) => i.href.includes("mailto:"));
        const email = emailLink ? emailLink.innerText.trim() : null;
        if (email) {
          return `${firstName},${email},${name},${nick}\n`;
        }
      }
      return null;
    })
    .filter((i) => i);

  users = users.concat(cards);

  if (users.length < totalMin) {
    console.log(`${users.length} e-mails recuperados até agora`);
    const nextBtn = [].slice
      .call(document.getElementsByClassName("next_page"))
      .filter((i) => !i.classList.contains("disabled"));

    if (nextBtn.length) {
      nextBtn[0].click();
      setTimeout(() => {
        find(totalMin, users);
      }, random(5000, 10000));
    } else {
      console.log(`${users.length} e-mails recuperados`);
      print(users);
    }
  } else {
    console.log(`${users.length} e-mails recuperados`);
    print(users);
  }
};

const print = (users) => {
  csvData = new Blob(users, {
    type: "text/csv",
  });
  var csvUrl = URL.createObjectURL(csvData);

  var link = document.createElement("a");
  link.href = csvUrl;
  link.download = "emails.csv";
  link.click();
};
